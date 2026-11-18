import os
from flask import Flask, request, jsonify
import requests
from requests.auth import HTTPBasicAuth
import datetime
import base64

app = Flask(__name__)

# Your credentials
consumer_key = os.environ["SBA_MPESA_KEY"]
consumer_secret = os.environ["SBA_MPESA_SECRET"]
shortcode = os.environ["SBA_MPESA_SHORTCODE"]
passkey = os.environ["SBA_MPESA_PASSKEY"]
callback_url = os.environ["SBA_MPESA_CALLBACK_URL"]  # Replace with your ngrok URL

def generate_oauth_token(consumer_key, consumer_secret):
    url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
    response = requests.get(url, auth=HTTPBasicAuth(consumer_key, consumer_secret))
    token = response.json()['access_token']
    return token

def initiate_stk_push(phone_number, amount, token, shortcode, passkey, callback_url):
    timestamp = datetime.datetime.now().strftime('%Y%m%d%H%M%S')
    password = base64.b64encode(f"{shortcode}{passkey}{timestamp}".encode('utf-8')).decode('utf-8')
    
    headers = {
        'Authorization': f'Bearer {token}',
        'Content-Type': 'application/json'
    }
    
    payload = {
        "BusinessShortCode": shortcode,
        "Password": password,
        "Timestamp": timestamp,
        "TransactionType": "CustomerPayBillOnline",
        "Amount": amount,
        "PartyA": phone_number,
        "PartyB": shortcode,
        "PhoneNumber": phone_number,
        "CallBackURL": callback_url,
        "AccountReference": "SBA Awards",
        "TransactionDesc": f"Voting {amount // 10} votes"
    }
    
    url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
    response = requests.post(url, json=payload, headers=headers)
    
    return response.json()

@app.route('/process_payment', methods=['POST'])
def process_payment():
    data = request.json
    phone_number = data['phoneNumber']
    votes = data['votes']
    amount = votes * 10
    
    # Generate OAuth token
    token = generate_oauth_token(consumer_key, consumer_secret)
    
    # Initiate STK Push
    response = initiate_stk_push(phone_number, amount, token, shortcode, passkey, callback_url)
    
    if response.get('ResponseCode') == '0':
        return jsonify(success=True, message="STK Push initiated")
    else:
        return jsonify(success=False, message="Failed to initiate STK Push")

# Example callback endpoint
@app.route('/callback', methods=['POST'])
def mpesa_callback():
    data = request.json
    # Process the callback data as needed
    print("Callback received:", data)
    return jsonify({"ResultCode": 0, "ResultDesc": "Success"})

if __name__ == '__main__':
    app.run(debug=True, port=5000)

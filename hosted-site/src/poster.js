const canvas = document.querySelector('#campaign-poster');
const button = document.querySelector('#download-poster');
const logo = document.querySelector('#poster-logo');
const status = document.querySelector('#poster-status');

if (canvas && button) {
    const context = canvas.getContext('2d');
    if (!context) {
        status.textContent = 'Poster generation is unavailable in this browser.';
    } else {
        const gold='#edca66', cream='#fff6dd', green='#0a2118';
        function text(value, x, y, size, color, font='Georgia', weight='400') {
            context.font=`${weight} ${size}px ${font}`;
            context.fillStyle=color;
            context.fillText(value,x,y);
        }
        function wrapped(value,maxWidth,initial,maxLines) {
            let size=initial;
            let lines=[];
            while (size>=22) {
                context.font=`700 ${size}px Georgia`;
                lines=[];
                let line='';
                for (const word of value.trim().split(/\s+/)) {
                    let segment='';
                    for (const character of word) {
                        if (segment && context.measureText(segment+character).width>maxWidth) {
                            if (line) lines.push(line);
                            lines.push(segment);
                            line=''; segment='';
                        }
                        segment+=character;
                    }
                    const next=line ? line+' '+segment : segment;
                    if (line && context.measureText(next).width>maxWidth) { lines.push(line);line=segment; }
                    else line=next;
                }
                if (line) lines.push(line);
                if (lines.length<=maxLines) return {lines,size};
                size-=3;
            }
            const visible=lines.slice(0,maxLines);
            visible[maxLines-1]=(visible[maxLines-1]||'').slice(0,-2)+'…';
            return {lines:visible,size:22};
        }
        function draw() {
            const background=context.createLinearGradient(100,0,1000,1350);
            background.addColorStop(0,'#1b4733');
            background.addColorStop(.6,green);
            background.addColorStop(1,'#0d1711');
            context.fillStyle=background;
            context.fillRect(0,0,1080,1350);
            context.strokeStyle='#b79a56';
            context.lineWidth=2;
            context.strokeRect(43,43,994,1264);
            context.strokeStyle='#b79a5644';
            context.strokeRect(59,59,962,1232);
            if (logo?.complete && logo.naturalWidth) context.drawImage(logo,88,95,128,128);
            text('SBA AWARDS',242,146,39,cream,'Georgia','700');
            text('STARTUPS BUSINESSES AFRICA AWARDS',243,189,20,gold,'sans-serif','700');
            text('OFFICIAL NOMINEE',88,324,27,gold,'sans-serif','700');
            const name=wrapped(canvas.dataset.name,900,104,3);
            name.lines.forEach((line,i)=>text(line,88,458+i*name.size*1.08,name.size,cream,'Georgia','700'));
            if (canvas.dataset.subtitle) {
                const subtitle=wrapped(canvas.dataset.subtitle,900,34,1);
                text(subtitle.lines[0],88,704,subtitle.size,cream,'Georgia');
            }
            const category=wrapped(canvas.dataset.category,900,45,2);
            category.lines.forEach((line,i)=>text(line,88,745+i*52,category.size,gold,'Georgia','700'));
            context.fillStyle='#14382a';
            context.fillRect(88,849,904,302);
            text('HOW TO VOTE',126,913,25,gold,'sans-serif','700');
            text('1. Open sba.tsols.co.ke/ballot',126,975,32,cream,'sans-serif','700');
            text('2. Sign in when voting is open.',126,1035,30,cream,'sans-serif');
            text('3. Find this category and select this nominee.',126,1093,28,cream,'sans-serif');
            text('One free voting credit per day.',88,1233,29,cream,'sans-serif');
            button.disabled=false;
        }
        if (logo && !logo.complete) {
            logo.addEventListener('load',draw,{once:true});
            logo.addEventListener('error',draw,{once:true});
        } else draw();

        button.addEventListener('click',()=>{
            canvas.toBlob(blob=>{
                if (!blob) {
                    status.textContent='The poster could not be downloaded. Try again.';
                    return;
                }
                const url=URL.createObjectURL(blob);
                const download=document.createElement('a');
                download.href=url;
                download.download='sba-awards-'+(canvas.dataset.name.toLowerCase().replace(/[^a-z0-9]+/g,'-')
                    .replace(/^-|-$/g,'').slice(0,65)||'nominee')+'.png';
                download.click();
                setTimeout(()=>URL.revokeObjectURL(url),10000);
                status.textContent='Your poster is ready to share.';
            },'image/png');
        });
    }
}

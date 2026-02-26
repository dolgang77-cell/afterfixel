const fs = require('fs');
const htmlPath = 'd:\\@1작업\\무인호텔\\html02\\index.html';
let html = fs.readFileSync(htmlPath, 'utf8');

html = html.replace(/<div class="bnum"><img src="images\/bnum10\.png"><\/div>/g, '<div class="bnum bnum-10"><img src="images/bnum10.png"></div>');

fs.writeFileSync(htmlPath, html, 'utf8');
console.log('updated index.html');

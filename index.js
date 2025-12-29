const express = require('express');
const app = express();

// 1. FIXED Wildcard tracking — catches /i (with or without slash) + ANYTHING after (/?params, /anything, #fragments)
app.get(/^\/i(\/.*)?$/, (req, res) => {
  const fullUrl = 'https://amnistoe.dart.page' + req.originalUrl;
  const params = req.query;
  let decoded = '';
  if (req.url.includes('#')) {
    try { decoded = atob(req.url.split('#')[1]); } catch(e) {}
  }

  res.send(`
    <h3>✅ Tracking Link Active (Fixed!)</h3>
    <p><b>Full URL:</b><br><code>${fullUrl}</code></p>
    <p><b>Parameters:</b><br><pre>${JSON.stringify(params, null, 2)}</pre></p>
    ${decoded ? <p><b>Decoded fragment:</b><br><code>${decoded}</code></p> : ''}
    <hr><small><a href="/">← Back to main site</a></small>
  `);
});

// 2. Serve ALL your website files (index.html + css + js + images)
app.use(express.static(__dirname));

// 3. Make every other URL show your main website
app.get('*', (req, res) => {
  res.sendFile(__dirname + '/index.html');
});


app.listen(process.env.PORT || 3000);


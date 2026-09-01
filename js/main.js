/* ============================================================
   AION-IA - Shared chrome, navigation, reveal, canvas visuals
   ============================================================ */

(function(){

  var NAV_LINKS = [
    { group: "Research", items: [
      ["Physics Guardrail", "physics-guardrail.html"],
      ["Engines", "engines.html"],
      ["Quantum IDE", "quantum-ide.html"]
    ]},
    { group: "Company", items: [
      ["Academic Collaboration", "academic-collaboration.html"],
      ["Whitepapers", "whitepapers.html"],
      ["Products", "products.html"],
      ["Careers", "careers.html"],
      ["Contact", "contact.html"]
    ]}
  ];

  function renderHeader(){
    var host = document.getElementById("site-header");
    if(!host) return;
    var navHtml = NAV_LINKS.map(function(col){
      var links = col.items.map(function(it){ return '<a href="'+it[1]+'">'+it[0]+'</a>'; }).join("");
      return '<div><div class="nav-col-label">'+col.group+'</div>'+links+'</div>';
    }).join("");

    host.innerHTML =
      '<header class="site-header">' +
        '<a class="brand" href="index.html"><img class="brand-logo" src="assets/logo.png" alt="AION-IA"><span>AION-IA</span></a>' +
        '<button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="siteNav">' +
          '<span class="lines"><span></span><span></span></span>' +
          '<span id="navToggleLabel">MENU</span>' +
        '</button>' +
      '</header>' +
      '<nav class="site-nav" id="siteNav">' +
        '<div class="site-nav-grid">'+navHtml+'</div>' +
      '</nav>';

    var toggle = document.getElementById("navToggle");
    var label = document.getElementById("navToggleLabel");
    toggle.addEventListener("click", function(){
      var open = document.body.classList.toggle("nav-open");
      toggle.setAttribute("aria-expanded", open ? "true":"false");
      label.textContent = open ? "CLOSE" : "MENU";
    });
    document.getElementById("siteNav").addEventListener("click", function(e){
      if(e.target.tagName === "A"){ document.body.classList.remove("nav-open"); label.textContent="MENU"; }
    });
    document.addEventListener("keydown", function(e){
      if(e.key === "Escape"){ document.body.classList.remove("nav-open"); label.textContent="MENU"; }
    });
  }

  function renderFooter(){
    var host = document.getElementById("site-footer");
    if(!host) return;
    host.innerHTML =
      '<footer class="site-footer"><div class="wrap">' +
        '<div class="footer-visual">' + footerSvg() + '</div>' +
        '<div class="footer-grid">' +
          '<div class="footer-brand">' +
            '<div class="brand" style="color:var(--text)">AION&#8209;IA</div>' +
            '<p>Quantum Research Company.<br>Electronic City, Bangalore, India.</p>' +
          '</div>' +
          '<div class="footer-col">' +
            '<div class="footer-col-label">Research</div>' +
            '<a href="index.html#materials">Materials &amp; Sensing</a>' +
            '<a href="index.html#comms">Communications &amp; PQC</a>' +
            '<a href="index.html#simulations">Simulations &amp; Optimisations</a>' +
            '<a href="index.html#qml">Quantum ML &amp; Healthcare</a>' +
          '</div>' +
          '<div class="footer-col">' +
            '<div class="footer-col-label">Explore</div>' +
            '<a href="physics-guardrail.html">Physics Guardrail</a>' +
            '<a href="engines.html">Engines</a>' +
            '<a href="quantum-ide.html">Quantum IDE</a>' +
            '<a href="academic-collaboration.html">Academic Collaboration</a>' +
            '<a href="whitepapers.html">Whitepapers</a>' +
            '<a href="products.html">Products</a>' +
            '<a href="careers.html">Careers</a>' +
          '</div>' +
          '<div class="footer-col">' +
            '<div class="footer-col-label">Connect</div>' +
            '<a href="https://www.instagram.com/aion.ia_quantumresearch/" target="_blank" rel="noopener">Instagram</a>' +
            '<a href="https://www.linkedin.com/company/101884329/admin/" target="_blank" rel="noopener">LinkedIn</a>' +
            '<a href="https://discord.gg/fPGuV9WvK" target="_blank" rel="noopener">Discord</a>' +
            '<a href="mailto:info@aion-ia.in">info@aion-ia.in</a>' +
          '</div>' +
        '</div>' +
        '<div class="footer-bottom">' +
          '<span>&copy; ' + new Date().getFullYear() + ' AION-IA. All rights reserved.</span>' +
          '<span><a class="text-link" href="privacy-policy.html">Privacy Policy</a> &nbsp; <a class="text-link" href="terms.html">Terms &amp; Conditions</a></span>' +
        '</div>' +
      '</div></footer>';
  }

  function footerSvg(){
    return '<svg width="120" height="48" viewBox="0 0 120 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' +
      '<circle cx="60" cy="24" r="3" stroke="var(--muted-2)"/>' +
      '<ellipse cx="60" cy="24" rx="46" ry="14" stroke="var(--line)"/>' +
      '<ellipse cx="60" cy="24" rx="46" ry="14" stroke="var(--line)" transform="rotate(60 60 24)"/>' +
      '<ellipse cx="60" cy="24" rx="46" ry="14" stroke="var(--line)" transform="rotate(120 60 24)"/>' +
      '<circle cx="60" cy="24" r="1.6" fill="var(--accent-dim)"/>' +
      '</svg>';
  }

  /* ---------------- Scroll reveal ---------------- */
  function initReveal(){
    var items = document.querySelectorAll(".reveal");
    if(!("IntersectionObserver" in window) || !items.length){
      items.forEach(function(el){ el.classList.add("is-visible"); });
      return;
    }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){ entry.target.classList.add("is-visible"); io.unobserve(entry.target); }
      });
    }, { threshold:.18 });
    items.forEach(function(el){ io.observe(el); });
  }

  /* ---------------- Header contrast on scroll (mix-blend already handles most) ---------------- */

  /* ---------------- Canvas: hero quantum-state visual ---------------- */
  function initHeroCanvas(){
    var wrap = document.querySelector(".hero-canvas-wrap");
    if(!wrap) return;
    var canvas = document.createElement("canvas");
    wrap.appendChild(canvas);
    var ctx = canvas.getContext("2d");
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var w,h,dpr;
    var nodes = [];
    var N = window.innerWidth < 720 ? 26 : 46;

    function resize(){
      w = wrap.offsetWidth; h = wrap.offsetHeight; dpr = Math.min(window.devicePixelRatio||1, 2);
      canvas.width = w*dpr; canvas.height = h*dpr; canvas.style.width=w+"px"; canvas.style.height=h+"px";
      ctx.setTransform(dpr,0,0,dpr,0,0);
    }
    function seed(){
      nodes = [];
      for(var i=0;i<N;i++){
        nodes.push({
          x: Math.random()*w, y: Math.random()*h*0.85,
          r: 1 + Math.random()*1.6,
          vx: (Math.random()-0.5)*0.06, vy: (Math.random()-0.5)*0.06,
          phase: Math.random()*Math.PI*2
        });
      }
    }
    function dist(a,b){ return Math.hypot(a.x-b.x, a.y-b.y); }

    var t = 0;
    function frame(){
      t += 0.006;
      ctx.clearRect(0,0,w,h);
      ctx.strokeStyle = "rgba(205,191,154,0.16)";
      ctx.lineWidth = 1;
      for(var i=0;i<nodes.length;i++){
        var a = nodes[i];
        a.x += a.vx; a.y += a.vy;
        if(a.x<0||a.x>w) a.vx*=-1;
        if(a.y<0||a.y>h*0.9) a.vy*=-1;
        for(var j=i+1;j<nodes.length;j++){
          var b = nodes[j];
          var d = dist(a,b);
          if(d < 130){
            ctx.globalAlpha = (1 - d/130) * 0.5;
            ctx.beginPath(); ctx.moveTo(a.x,a.y); ctx.lineTo(b.x,b.y); ctx.stroke();
          }
        }
      }
      ctx.globalAlpha = 1;
      for(i=0;i<nodes.length;i++){
        a = nodes[i];
        var pulse = 0.6 + 0.4*Math.sin(t*2 + a.phase);
        ctx.beginPath();
        ctx.fillStyle = "rgba(242,241,237,"+(0.35+0.35*pulse)+")";
        ctx.arc(a.x, a.y, a.r*pulse, 0, Math.PI*2);
        ctx.fill();
      }
      if(!reduced) requestAnimationFrame(frame);
    }
    window.addEventListener("resize", function(){ resize(); seed(); });
    resize(); seed(); frame();
  }

  /* ---------------- Canvas: generic domain background (lattice / mesh) ---------------- */
  function initDomainCanvases(){
    var canvases = document.querySelectorAll(".domain-canvas");
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    canvases.forEach(function(host){
      var mode = host.getAttribute("data-mode") || "lattice";
      var canvas = document.createElement("canvas");
      host.appendChild(canvas);
      var ctx = canvas.getContext("2d");
      var w,h,dpr,t=0;
      function resize(){
        w = host.offsetWidth; h = host.offsetHeight; dpr = Math.min(window.devicePixelRatio||1,2);
        canvas.width=w*dpr; canvas.height=h*dpr; canvas.style.width=w+"px"; canvas.style.height=h+"px";
        ctx.setTransform(dpr,0,0,dpr,0,0);
      }
      function drawLattice(){
        ctx.clearRect(0,0,w,h);
        var gap = 46;
        ctx.strokeStyle = "rgba(148,147,140,0.14)";
        for(var x=0; x<w+gap; x+=gap){
          for(var y=0; y<h+gap; y+=gap){
            var off = Math.sin(t + x*0.02 + y*0.02) * 4;
            ctx.beginPath();
            ctx.arc(x + off, y + off*0.4, 1.3, 0, Math.PI*2);
            ctx.fillStyle = "rgba(205,191,154,0.22)";
            ctx.fill();
            if(x+gap < w+gap){ ctx.beginPath(); ctx.moveTo(x+off,y+off*0.4); ctx.lineTo(x+gap+off,y+off*0.4); ctx.stroke(); }
          }
        }
      }
      function drawWaves(){
        ctx.clearRect(0,0,w,h);
        ctx.strokeStyle = "rgba(205,191,154,0.18)";
        for(var i=0;i<7;i++){
          ctx.beginPath();
          for(var x=0;x<=w;x+=8){
            var y = h/2 + (i-3)*26 + Math.sin(x*0.02 + t*1.4 + i)*14;
            if(x===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
          }
          ctx.stroke();
        }
      }
      function drawMesh(){
        ctx.clearRect(0,0,w,h);
        var cols = 14, rows = 8;
        var pts = [];
        for(var i=0;i<=cols;i++){
          pts[i]=[];
          for(var j=0;j<=rows;j++){
            var bx = (w/cols)*i, by = (h/rows)*j;
            var n = Math.sin(bx*0.01 + by*0.02 + t) * 10;
            pts[i][j] = [bx+n, by+n*0.5];
          }
        }
        ctx.strokeStyle = "rgba(148,147,140,0.16)";
        for(i=0;i<=cols;i++) for(j=0;j<=rows;j++){
          if(i<cols){ ctx.beginPath(); ctx.moveTo(pts[i][j][0],pts[i][j][1]); ctx.lineTo(pts[i+1][j][0],pts[i+1][j][1]); ctx.stroke(); }
          if(j<rows){ ctx.beginPath(); ctx.moveTo(pts[i][j][0],pts[i][j][1]); ctx.lineTo(pts[i][j+1][0],pts[i][j+1][1]); ctx.stroke(); }
        }
      }
      function drawCircuit(){
        ctx.clearRect(0,0,w,h);
        ctx.strokeStyle = "rgba(148,147,140,0.18)";
        var rows = 5;
        for(var r=0;r<rows;r++){
          var y = (h/(rows+1))*(r+1);
          ctx.beginPath(); ctx.moveTo(0,y); ctx.lineTo(w,y); ctx.stroke();
          for(var x=40; x<w; x+=90){
            var gx = x + ((r%2)?20:0);
            var pulse = (Math.sin(t*1.6 + r + x*0.01) + 1)/2;
            ctx.fillStyle = "rgba(205,191,154,"+(0.15+pulse*0.35)+")";
            ctx.fillRect(gx-8, y-8, 16, 16);
          }
        }
      }
      function draw(){
        t += 0.01;
        if(mode==="lattice") drawLattice();
        else if(mode==="waves") drawWaves();
        else if(mode==="mesh") drawMesh();
        else if(mode==="circuit") drawCircuit();
        if(!reduced) requestAnimationFrame(draw);
      }
      window.addEventListener("resize", resize);
      resize(); draw();
    });
  }

  /* ---------------- Engine core SVG ---------------- */
  function initEngineCore(){
    var host = document.getElementById("engineCoreVisual");
    if(!host) return;
    host.innerHTML = '<svg viewBox="0 0 300 160" width="100%" height="160" aria-hidden="true">' +
      '<g stroke="var(--line)" fill="none">' +
      '<ellipse cx="150" cy="80" rx="120" ry="30"/>' +
      '<ellipse cx="150" cy="80" rx="120" ry="30" transform="rotate(60 150 80)"/>' +
      '<ellipse cx="150" cy="80" rx="120" ry="30" transform="rotate(120 150 80)"/>' +
      '</g>' +
      '<circle cx="150" cy="80" r="5" fill="var(--accent)"/>' +
      '</svg>';
  }

  document.addEventListener("DOMContentLoaded", function(){
    renderHeader();
    renderFooter();
    initReveal();
    initHeroCanvas();
    initDomainCanvases();
    initEngineCore();
  });

})();

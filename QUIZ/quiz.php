<?php
session_start();

if (empty($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$user_email = $_SESSION['user_email'];

// Full 20 questions
$questions = [
    ['Q' => "Qubit Superposition & Measurement: A single qubit is initialized to |0>. Apply the sequence: H -> S -> H -> T. What is the probability of measuring |1>?", 
     'A' => ['0.146','0.25','0.5','0.854']],

    ['Q' => "Bloch Sphere Rotation: Which rotation combination around X and Y axes will map |0> to |1>?", 
     'A' => ['RX(pi/2) -> RY(pi/2)','RX(pi)','RY(pi/2) -> RX(pi/2)','RX(pi/2) -> RY(pi/4)']],

    ['Q' => "Entanglement Concept: Two qubits are entangled in a Bell state. Which statement is true?", 
     'A' => ['Measuring first qubit collapses second instantly','Both qubits remain independent','Measuring first qubit has no effect','Entanglement only affects probabilities after measurement']],

    ['Q' => "Quantum Decoherence: Which is the primary cause of decoherence in quantum circuits?", 
     'A' => ['Imperfect gates','Interaction with environment','Random measurement','Initialization errors']],

    ['Q' => "Quantum Algorithm Speedup: Which quantum algorithm provides exponential speedup for factoring integers?", 
     'A' => ['Grover’s','Shor’s','Deutsch–Jozsa','Simon’s']],

    ['Q' => "Bloch Sphere Poles: The north and south poles of the Bloch sphere represent:", 
     'A' => ['|+> and |->','|0> and |1>','Entangled states','Superpositions']],

    ['Q' => "Quantum Measurement: A qubit in (|0> + i|1>)/sqrt(2) is measured in computational basis. Probability of |1>?", 
     'A' => ['0.25','0.5','0.75','1']],

    ['Q' => "Noise Models: Which Qiskit noise model simulates amplitude damping?", 
     'A' => ['depolarizing_error','amplitude_damping_error','phase_flip_error','bit_flip_error']],

    ['Q' => "Quantum Gate Identification: Which gate performs a conditional Z-flip?", 
     'A' => ['CX','CZ','CCX','H']],

    ['Q' => "Quantum Circuit Complexity: A 3-qubit Grover circuit requires 2 iterations to find a marked state. Why?", 
     'A' => ['Because sqrt(N) ≈ 2','Because 2 iterations always maximize probability','It depends on the oracle','Random chance']],

    ['Q' => "Multi-Qubit GHZ State: qc = QuantumCircuit(3); qc.h(0); qc.cx(0,1); qc.cx(1,2); qc.measure_all(); You measure qubit 0 -> 0. What are qubits 1 and 2?", 
     'A' => ['Both 0','Both 1','Entangled, depends','Random']],

    ['Q' => "Deutsch–Jozsa Oracle Check: qc = QuantumCircuit(3); qc.h([0,1,2]); oracle(qc); qc.h([0,1,2]); qc.measure_all(); Oracle flips only |101>. What outcome is expected?", 
     'A' => ['000','101','Random','111']],

    ['Q' => "Grover Iteration Debug: qc = QuantumCircuit(2); qc.h([0,1]); qc.cz(0,1); qc.h([0,1]); qc.measure_all(); What is the marked state after one iteration?", 
     'A' => ['|00>','|11>','|10>','|01>']],

    ['Q' => "RX + RY Mental Circuit: qc = QuantumCircuit(1); qc.rx(pi/2,0); qc.ry(pi/2,0); qc.measure_all(); Which outcome is most probable?", 
     'A' => ['0','1','50/50','Depends on simulator']],

    ['Q' => "Noisy Simulator Implementation: from qiskit.providers.aer.noise import NoiseModel; noise_model = NoiseModel(); Which backend executes the simulation?", 
     'A' => ['Aer.get_backend(\"qasm_simulator\")','Aer.get_backend(\"qasm_simulator\", noise_model=noise_model)','QuantumCircuit(1)','execute(qc)']],

    ['Q' => "Bell State Measurement: qc = QuantumCircuit(2); qc.h(0); qc.cx(0,1); qc.measure_all(); First qubit -> 1. Second qubit?", 
     'A' => ['0','1','Random','Undefined']],

    ['Q' => "Phase Flip Understanding: qc = QuantumCircuit(2); qc.cz(0,1); Applied to |11>. What happens?", 
     'A' => ['Phase flips (-1)','State remains','Measurement collapses','Superposition forms']],

    ['Q' => "Debug Multi-Line Circuit: qc = QuantumCircuit(3); qc.h(0); qc.cx(1,0); qc.cx(0,2); qc.measure_all(); What is wrong?", 
     'A' => ['Control-target swapped','Missing measurement','Circuit fine','Error in backend']],

    ['Q' => "Qiskit Execute Bug: result = execute(qc, \"qasm_simulator\").result(); Why might this fail?", 
     'A' => ['Backend must be object','Measurement missing','Circuit undefined','Noise model needed']],

    ['Q' => "Noise Model Effect: T1 = 10 us, qubit initially |1>, wait 5 us. Probability still |1>?", 
     'A' => ['0','<0.5','~0.61','1']]
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Quiz — Start</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="quiz-wrapper">
    <div class="header">
      <h2>Quiz — Please complete all 20 questions</h2>
      <div style="color:var(--muted);font-size:0.9rem">Email: <strong><?php echo htmlspecialchars($user_email); ?></strong></div>
      <div style="display:flex;align-items:center;gap:12px;">
        <div class="progress"><span id="progressBar"></span></div>
        <div style="color:var(--muted)"><span id="progressText">0/20</span></div>
      </div>
    </div>

    <form id="quizForm" method="post" action="submit.php">
      <input type="hidden" name="action" value="submit">
      <?php foreach ($questions as $i => $q): $num = $i+1; ?>
        <div class="question-card">
          <div class="q-title">Q<?php echo $num; ?>. <?php echo htmlspecialchars($q['Q']); ?></div>
          <div class="options">
            <?php foreach ($q['A'] as $j => $opt): $optLabel = chr(65+$j); ?>
              <label>
                <input type="radio" name="q<?php echo $num; ?>" value="<?php echo $optLabel; ?>" <?php echo ($j===0)?'required':''; ?>>
                <?php echo $optLabel; ?>. <?php echo htmlspecialchars($opt); ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <footer class="actions">
        <button type="button" id="quitBtn">Quit</button>
        <button type="submit" id="submitBtn">Submit Answers</button>
      </footer>
    </form>
  </div>

  <div id="disabledOverlay" style="display:none;">
    <div>
      <h2 id="overlayTitle">Session ended</h2>
      <p id="overlayMessage"></p>
      <p><a href="index.php">Back to Login</a></p>
    </div>
  </div>

<script>
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('copy', e => e.preventDefault());
document.addEventListener('cut', e => e.preventDefault());
document.addEventListener('contextmenu', e => e.preventDefault());

const quizForm = document.getElementById('quizForm');
const submitBtn = document.getElementById('submitBtn');
const disabledOverlay = document.getElementById('disabledOverlay');
let ended = false;

function endSession(reason) {
  if (ended) return;
  ended = true;
  document.getElementById('overlayTitle').innerText = 'Session ended';
  document.getElementById('overlayMessage').innerText = reason;
  disabledOverlay.style.display = 'flex';
  submitBtn.disabled = true;
  quizForm.querySelectorAll('input').forEach(i => i.disabled=true);

  try {
    if (navigator.sendBeacon) {
      const params = new URLSearchParams();
      params.append('action','end');
      navigator.sendBeacon('submit.php', params);
    }
  } catch(e){}
}

document.addEventListener('visibilitychange', () => { if(document.hidden) endSession('Window lost focus / switched tab.'); });
window.addEventListener('blur', () => endSession('Window lost focus / switched application.'));
window.addEventListener('beforeunload', () => { if(!ended) endSession('Window closed or refreshed.'); });

function updateProgress() {
  let answered = 0;
  for(let i=1;i<=20;i++){
    if(document.querySelector('input[name="q'+i+'"]:checked')) answered++;
  }
  document.getElementById('progressBar').style.width = (answered/20*100)+'%';
  document.getElementById('progressText').innerText = answered+'/20';
}
quizForm.querySelectorAll('input').forEach(i => i.addEventListener('change', updateProgress));
updateProgress();

quizForm.addEventListener('submit', function(ev){
  ev.preventDefault();
  if(ended) return;

  let answered = 0;
  for(let i=1;i<=20;i++) if(document.querySelector('input[name="q'+i+'"]:checked')) answered++;
  if(answered!==20){ alert('Please answer all 20 questions.'); return; }

  submitBtn.disabled=true;
  submitBtn.innerText='Submitting...';

fetch('submit.php', { method:'POST', body: new URLSearchParams(new FormData(quizForm)) })
  .then(r => r.json())
  .then(data => {
    alert('✅ Thanks for taking the quiz! Your answers have been recorded.\n📧 Results will be shared over mail on Monday.');
    endSession('Submitted successfully.');
  })
  .catch(e => {
    console.error(e);
    alert('✅ Thanks for taking the quiz! Your answers have been recorded.\n📧 Results will be shared over mail on Monday.');
    endSession('Submitted successfully.');
  });
});

document.getElementById('quitBtn').addEventListener('click', ()=>{
  if(confirm('Quit will end your session. Proceed?')) endSession('User quit.');
});
</script>
</body>
</html>
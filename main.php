<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <title>Face detection and screen capture</title>
  <style>
  </style>
</head>

<body class="container-fuild">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">
      <h1>Face detection</h1>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <!-- <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a> -->
        </li>
      </ul>
      <form class="form-inline my-2 my-lg-0">
        <button type="button" class="btn btn-success" id="btn_webcam">Share webcam</button>&nbsp;&nbsp;

        <button type="button" class="btn btn-primary" id="btn_screen">Share screen</button>&nbsp;&nbsp;
        <a href= "/facedetected/index.html" type="button" class="btn btn-danger" id="btn_screen">Log out</a>
      </form>
    </div>
  </nav>
  <div class="container">
    <div class="row" style="padding:5px">
      <video id="video" class="col-8" style="border:1px solid black" width="640" height="480" autoplay muted></video>

      <div class="col-4" style="padding:5px">
        <img src="default_user.png" alt="Face detected" style="border:1px solid black" id="face" />

      </div>
    </div>
    <div class="row" id="divResult" style="padding-top:10">
      <img src="" alt="Screen shot" style="border:1px solid black" width="480" height="360" id="screenshot" />

      <canvas id="canvas" style="overflow:auto"></canvas>
    </div>
  </div>
</body>
<script defer src="face_api.min.js"></script>
<!-- <script defer src="script.js"></script> -->
<script defer src="js/jquery-3.4.1.min.js"></script>
<script>
  /**
   * this script is for face detetion and screenshoot
   * if system detect face front of camera
   * then he take a screen shot of an active windows
   */
  const video = document.getElementById('video');

  const screenEl = document.createElement('video');
  screenEl.id = "screen";
  screenEl.autoplay = "autoplay";

  document.getElementById('divResult').appendChild(screenEl);
  const btn_webcame = document.getElementById("btn_webcam");
  const btn_screen = document.getElementById("btn_screen");
  const screen = screenEl;
  const canvas_screen = document.getElementById('canvas');
  const image_face = document.getElementById('face');
  const screen_shot = document.getElementById('screenshot');

  var display_surface = "";
  var nbr_face = 0;

  var webcam_is = false;
  var screen_is = false;

  var face_data = null;
  var screen_data = null;

  const constraints = {
    video: true
  };
  const display_media_options = {
    video: {
      cursor: "never",
      width: "640",
      height: "480"
    },
    audio: false
  };
  /**
   * loading a model for face detection
   * We use Tiny Face Detector
   */
  async function loadModel() {
    Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri('/facedetected/models')
    ]).then(startVideo)
  }
  /**
   * start video stream from web webcame
   */
  function startVideo() {
    if (navigator.mediaDevices.getUserMedia) {
      navigator.mediaDevices.getUserMedia(constraints).
      then(handleSuccess).catch(handleError);
    }
  }

  function handleSuccess(stream) {
    video.srcObject = stream;
    btn_webcame.innerHTML = "Stop sharing";
  }

  function handleError(error) {
    alert("Please give access to your Webcam to enjoy this course !");
  }

  btn_webcame.addEventListener("click", function(event) {
    event.preventDefault();
    /**
     * check if the webcam is open or not
     * and change the status of webcam_is
     */
    if (!webcam_is) {

      loadModel();
      webcam_is = true;
    } else {
      var stream = video.srcObject;
      var tracks = stream.getTracks();

      for (var i = 0; i < tracks.length; i++) {
        var track = tracks[i];
        track.stop();
      }
      video.srcObject = null;
      webcam_is = false;
      this.innerHTML = "Share webcam";
    }

    console.log("Webcam btn click");
  });

  btn_screen.addEventListener("click", function(event) {
    event.preventDefault();
    /**
     */
    if (!screen_is) {

      shareScreen();
      screen_is = true;
    } else {

    }
    console.log("Screen btn click");
  });

  video.addEventListener('play', () => {
    const canvas_video = faceapi.createCanvasFromMedia(video);
    const displaySize = {
      width: video.width,
      height: video.height
    };
    faceapi.matchDimensions(canvas_video, displaySize);

    setInterval(async () => {
      const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
      /**
       * count number of face detected in the stream
       */
      nbr_face = detections.length;
      const resizedDetections = faceapi.resizeResults(detections, displaySize);
      canvas_video.width = video.videoWidth;
      canvas_video.height = video.videoHeight;


      if (nbr_face > 0) {

        for (var i = 0; i < nbr_face; i++) {

          var _x = resizedDetections[i]["_box"]["_x"];
          var _y = resizedDetections[i]["_box"]["_y"];
          var _width = resizedDetections[i]["_box"]["_width"];
          var _height = resizedDetections[i]["_box"]["_height"];

          var face_ = document.createElement("canvas");
          face_.width = _width;
          face_.height = _height;

          var keys = Object.keys(resizedDetections);
          // console.log(keys); 
          // console.log(resizedDetections[i]["_box"]);
          // console.log(_x + " " + _y + " " + _width + " " + _height);

          var ctx = canvas_video.getContext('2d');
          ctx.drawImage(video, 0, 0);

          var imageData = ctx.getImageData(_x, _y, _width, _height);
          //ctx.putImageData(imageData, 10, 10);
          ctx.putImageData(imageData, 0, 0);
          var ctxx = face_.getContext("2d");
          ctxx.putImageData(imageData, 0, 0);
          image_face.src = face_.toDataURL("image/webp");
          face_data = face_.toDataURL("image/jpeg");
          //alert(face_data);
          //console.log(nbr_face + " detected");
          //faceapi.draw.drawDetections(video, resizedDetections);
          //mage_face.src = ctx.toDataURL('image/webp');
          console.log(nbr_face + " detected");

          if (screen_is === true) {

            canvas_screen.width = screen.videoWidth;
            canvas_screen.height = screen.videoHeight;
            canvas_screen.getContext('2d').drawImage(screen, 0, 0, screen.videoWidth, screen.videoHeight);
            screen_shot.src = canvas_screen.toDataURL('image/webp');
            screen_data = canvas_screen.toDataURL("image/png");
            console.log("width: " + canvas_screen.width);
            console.log("height: " + canvas_screen.height);
            //alert(screen_data);
            requestXHR(face_data, screen_data);
            uploadData(face_data, screen_data);
          }
        }
      } else {
        console.log(nbr_face + " detected");
        image_face.src = "no_face.png";
        face_data = null;
      }
    }, 6000)
  })
  /**
   * check if the use give permission to access
   * his creen with displaySurface == "monitor" for chrome
   */
  async function shareScreen() {

    try {
      screen.srcObject = await navigator.mediaDevices.getDisplayMedia(display_media_options);
      dumpOptionsInfo();
    } catch (err) {
      console.log("Access denide");
    }
  }

  function dumpOptionsInfo() {
    const videoTrack = screen.srcObject.getVideoTracks()[0];

    console.info("Track settings:");
    console.info(JSON.stringify(videoTrack.getSettings(), null, 2));
    console.info("Track constraints:");
    console.info(JSON.stringify(videoTrack.getConstraints(), null, 2));
    display_surface = videoTrack.getConstraints().mediaSource();
  }

  function requestXHR(face, screen) {
    console.log("<?php echo $_POST['studentId']; ?>");
    var dt = new Date();
    var date = `${dt.getFullYear().toString().padStart(4, '0')}-
    ${(dt.getMonth()+1).toString().padStart(2, '0')}-
      ${dt.getDate().toString().padStart(2, '0')}${
      dt.getHours().toString().padStart(2, '0')}:${
      dt.getMinutes().toString().padStart(2, '0')}:${
      dt.getSeconds().toString().padStart(2, '0')}`;
    console.log(date);

    const toSend = {
      //studentId: "205657", //steven
      // studentId: "03214",// nelka
      // studentId: "01452", // richard
      //  studentId: "203212",
      studentId: "<?php echo $_POST['studentId']; ?>",
      // timeInout: "2020-03-2610:31:31",
      timeInout: date,
      screenStatus: "1",
      imageInout: face,
      inoutState: "1",
      roomId: "204",
      nvrId: "1",
      cameraId: "9",
      macAddr: "99:99:XX:XX:XX:XX",
      screen: screen
    };
    const jsonString = JSON.stringify(toSend);

    var xhr = new XMLHttpRequest();
    const addr = "http://27.71.228.53:9002/";
    const url = addr + "SmartClass/student/facescreen";

    xhr.open("POST", url);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(jsonString);

    xhr.addEventListener('readystatechange', function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        console.log(xhr.status)
        console.log(xhr.status)
        var response = JSON.parse(xhr.responseText);
        console.log(response);
      }
    });
  }
  /**
   * 
   */
  function uploadData(face, screen) {
    var form = new FormData();
    form.append("face", face);
    form.append("screen", screen);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "ImageController.php");
    // xhr.setRequestHeader("Content-Type", "multipart/form-data");

    xhr.send(form);
    xhr.addEventListener("readystatechange", function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        console.log(xhr.responseText);
        console.log("status request : " + xhr.status)
      }
    });
  }
</script>

</html>
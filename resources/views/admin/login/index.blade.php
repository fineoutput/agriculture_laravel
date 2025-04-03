<!DOCTYPE HTML>
<html lang="zxx">

<head>
  <title>Login | {{config('constants.options.SITE_NAME')}}</title>
  <!-- Meta tag Keywords -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="icon" href="{{asset('admin/assets/images/favicon.png')}}">
  <meta name="keywords" content="" />
  <script>
    addEventListener("load", function() {
      setTimeout(hideURLbar, 0);
    }, false);

    function hideURLbar() {
      window.scrollTo(0, 1);
    }
  </script>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

  <!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

  <!-- Latest compiled JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <!-- Meta tag Keywords -->
  <!-- css files -->
  <link rel="stylesheet" href="{{asset('admin/login/css/style.css')}}" type="text/css" media="all" />
  <!-- Style-CSS -->
  <link rel="stylesheet" href="{{asset('admin/login/css/font-awesome.css')}}">
  <!-- Font-Awesome-Icons-CSS -->
  <!-- //css files -->
  <!-- web-fonts -->
  <link href="//fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i,800,800i" rel="stylesheet">
  <!-- //web-fonts -->
  <style>
    .pom-agile {
      display: flex;
    }

    span.fa {
      float: right;
      color: #FF5722;
      line-height: 1.5;
      /* margin-left: 10px; */
      margin-right: 10px;
    }
  </style>
</head>

<body>
  <div class="video-w3l">
    <div id="tsparticles"></div>
    <!--header-->
    <div class="header-w3l">
      <h1>
        <span>{{config('constants.options.SITE_NAME')}}</span>
        <span>L</span>ogin

      </h1>
    </div>
    <!--//header-->
    <div class="main-content-agile">
      <div class="sub-main-w3" style="box-shadow: 0px 0px 20px 0px rgb(153 153 153 / 75%)">
        <h2>Login Here
          <i class="fa fa-hand-o-down" aria-hidden="true"></i>
        </h2>


        <!-- show success and error messages -->
        @if (session('success'))
        <div class="alert alert-success" role="alert">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger" role="alert">
          {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </div>
        @endif
        <!-- End show success and error messages -->


        <form action="{{route('admin_login_process')}}" method="post">
          @csrf
          <div class="pom-agile">
            <span class="fa fa-user-o" aria-hidden="true"></span>
            <input placeholder="Username" name="email" class="user" type="email" required="">
          </div>
          <div class="pom-agile">
            <span class="fa fa-key" aria-hidden="true"></span>
            <input placeholder="Password" name="password" class="pass" type="password" required="">
          </div>
          <div class="sub-w3l">

            <!-- <a href="#" id="butpas">Forgot Password?</a> -->
            <div class="clear"></div>
          </div>
          <div class="right-w3l">
            <input type="submit" value="Login">
          </div>
        </form>
        <br />
        <form action="{{route('admin_change_password')}}" method="post">
          @csrf
          <div id="passrst1" style="display:none;">
            <div class="pom-agile">
              <span class="fa fa-user-o" aria-hidden="true"></span>
              <input placeholder="Enter Email to reset password" name="email" class="user" type="email" required="">
            </div>
            <div class="right-w3l">
              <input type="submit" value="Reset">
            </div>
          </div>
        </form>
      </div>
    </div>
    <!--//main-->
    <!--footer-->

    <!--//footer-->
  </div>

  <!-- js -->
  <script src="{{asset('admin/login/js/jquery-2.1.4.min.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@1.34.0/tsparticles.min.js"></script>
  <!-- //js -->
  <script>
    tsParticles.load("tsparticles", {
      fullScreen: {
        enable: true
      },
      fpsLimit: 60,
      particles: {
        groups: {
          z5000: {
            number: {
              value: 70
            },
            zIndex: {
              value: 5000
            }
          },
          z7500: {
            number: {
              value: 30
            },
            zIndex: {
              value: 75
            }
          },
          z2500: {
            number: {
              value: 50
            },
            zIndex: {
              value: 25
            }
          },
          z1000: {
            number: {
              value: 40
            },
            zIndex: {
              value: 10
            }
          }
        },
        number: {
          value: 200,
          density: {
            enable: false,
            value_area: 800
          }
        },
        color: {
          value: "#fff",
          animation: {
            enable: false,
            speed: 20,
            sync: true
          }
        },
        shape: {
          type: "circle"
        },
        opacity: {
          value: 1,
          random: false,
          animation: {
            enable: false,
            speed: 2,
            minimumValue: 0.1,
            sync: false
          }
        },
        size: {
          value: 3
        },
        links: {
          enable: false,
          distance: 100,
          color: "#ffffff",
          opacity: 0.4,
          width: 1
        },
        move: {
          angle: {
            value: 10,
            offset: 0
          },
          enable: true,
          speed: 5,
          direction: "right",
          random: false,
          straight: true,
          outModes: {
            default: "out"
          },
          attract: {
            enable: false,
            rotateX: 600,
            rotateY: 1200
          }
        },
        zIndex: {
          value: 5,
          opacityRate: 0.5
        }
      },
      interactivity: {
        detectsOn: "canvas",
        events: {
          onHover: {
            enable: false,
            mode: "repulse"
          },
          onClick: {
            enable: true,
            mode: "push"
          },
          resize: true
        },
        modes: {
          grab: {
            distance: 400,
            links: {
              opacity: 1
            }
          },
          bubble: {
            distance: 400,
            size: 40,
            duration: 2,
            opacity: 0.8
          },
          repulse: {
            distance: 200
          },
          push: {
            quantity: 4,
            groups: ["z5000", "z7500", "z2500", "z1000"]
          },
          remove: {
            quantity: 2
          }
        }
      },
      detectRetina: true,
      background: {
        color: "#000000",
        image: "",
        position: "50% 50%",
        repeat: "no-repeat",
        size: "cover"
      },
      emitters: {
        position: {
          y: 55,
          x: -30
        },
        rate: {
          delay: 7,
          quantity: 1
        },
        size: {
          width: 0,
          height: 0
        },
        particles: {
          shape: {
            type: "images",
            options: {
              images: [{
                  src: "https://particles.js.org/images/amongus_blue.png",
                  width: 205,
                  height: 267
                },
                {
                  src: "https://particles.js.org/images/amongus_cyan.png",
                  width: 207,
                  height: 265
                },
                {
                  src: "https://particles.js.org/images/amongus_green.png",
                  width: 204,
                  height: 266
                },
                {
                  src: "https://particles.js.org/images/amongus_lime.png",
                  width: 206,
                  height: 267
                },
                {
                  src: "https://particles.js.org/images/amongus_orange.png",
                  width: 205,
                  height: 265
                },
                {
                  src: "https://particles.js.org/images/amongus_pink.png",
                  width: 205,
                  height: 265
                },
                {
                  src: "https://particles.js.org/images/amongus_red.png",
                  width: 204,
                  height: 267
                },
                {
                  src: "https://particles.js.org/images/amongus_white.png",
                  width: 205,
                  height: 267
                }
              ]
            }
          },
          size: {
            value: 40
          },
          move: {
            speed: 10,
            outModes: {
              default: "destroy",
              left: "none"
            },
            straight: true
          },
          zIndex: {
            value: 0
          },
          rotate: {
            value: {
              min: 0,
              max: 360
            },
            animation: {
              enable: true,
              speed: 10,
              sync: true
            }
          }
        }
      }
    });
  </script>
</body>

</html>
job "store-notifier" {
  datacenters = ["dc1"]

  group "cli" {
    count = 1

    meta {
      run_uuid = "${uuidv4()}"
    }

    update {
      max_parallel     = 1
      min_healthy_time = "30s"
      auto_revert      = false
    }

    network {
      mode = "bridge"
    }

    task "bot-prio" {
      driver = "docker"

      config {
        image      = "<aws_id>.dkr.ecr.<aws_region>.amazonaws.com/store-notifier/cli:latest"
        force_pull = true
        command    = "sh"
        args       = ["run/scheduler-prio.sh"]

        mount {
          type     = "bind"
          target   = "/app/.env"
          source   = "<storage_path>/store-notifier/.env"
          readonly = true
          bind_options {
            propagation = "rshared"
          }
        }

        mount {
          type     = "bind"
          target   = "/app/database/db.sqlite"
          source   = "<storage_path>/store-notifier/db.sqlite"
          readonly = false
          bind_options {
            propagation = "rshared"
          }
        }
      }

      resources {
        cpu    = 50
        memory = 128
      }
    }

    task "bot-lame" {
      driver = "docker"

      config {
        image      = "<aws_id>.dkr.ecr.<aws_region>.amazonaws.com/store-notifier/cli:latest"
        force_pull = true
        command    = "sh"
        args       = ["run/scheduler-lame.sh"]

        mount {
          type     = "bind"
          target   = "/app/.env"
          source   = "<storage_path>/store-notifier/.env"
          readonly = true
          bind_options {
            propagation = "rshared"
          }
        }

        mount {
          type     = "bind"
          target   = "/app/database/db.sqlite"
          source   = "<storage_path>/store-notifier/db.sqlite"
          readonly = false
          bind_options {
            propagation = "rshared"
          }
        }
      }

      resources {
        cpu    = 50
        memory = 128
      }
    }
  }
}

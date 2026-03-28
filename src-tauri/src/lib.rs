use std::process::Command;

#[cfg_attr(mobile, tauri::mobile_entry_point)]
pub fn run() {
  tauri::Builder::default()
    .setup(|app| {
      // แอบยิงคำสั่งเปิดเซิร์ฟเวอร์ PHP Backend ขึ้นมารอ
      println!("🤖 Starting PHP Sidecar...");
      Command::new("php")
        .args(["artisan", "serve", "--port=8000"])
        .spawn()
        .expect("Failed to start PHP server");

      #[cfg(debug_assertions)]
      {
        app.handle().plugin(
          tauri_plugin_log::Builder::default()
            .level(log::LevelFilter::Info)
            .build(),
        )?;
      }
      Ok(())
    })
    .run(tauri::generate_context!())
    .expect("error while running tauri application");
}

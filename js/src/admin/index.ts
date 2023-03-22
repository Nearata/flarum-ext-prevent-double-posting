import app from "flarum/admin/app";

app.initializers.add("nearata-prevent-double-posting", () => {
  app.extensionData
    .for("nearata-prevent-double-posting")
    .registerSetting({
      setting: "nearata-prevent-double-posting.except_thread_author",
      type: "boolean",
      label: app.translator.trans(
        "nearata-prevent-double-posting.admin.settings.except_thread_author.label"
      ),
      help: app.translator.trans(
        "nearata-prevent-double-posting.admin.settings.except_thread_author.help"
      ),
    })
    .registerSetting({
      setting: "nearata-prevent-double-posting.sequential_replies_threshold",
      type: "number",
      label: app.translator.trans(
        "nearata-prevent-double-posting.admin.settings.sequential_replies_threshold.label"
      ),
      help: app.translator.trans(
        "nearata-prevent-double-posting.admin.settings.sequential_replies_threshold.help"
      ),
    })
    .registerPermission(
      {
        icon: "fas fa-newspaper",
        label: app.translator.trans(
          "nearata-prevent-double-posting.admin.permissions.bypass_double_posting_label"
        ),
        permission: "nearata-prevent-double-posting.bypassDoublePosting",
      },
      "reply"
    );
});

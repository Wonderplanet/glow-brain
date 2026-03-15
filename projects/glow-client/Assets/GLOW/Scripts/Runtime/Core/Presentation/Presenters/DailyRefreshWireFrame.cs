using GLOW.Modules.MessageView.Presentation;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Core.Presentation.Presenters
{
    public class DailyRefreshWireFrame
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        public void ShowTitleBackView()
        {
            MessageViewUtil.ShowMessageWithOk(
            "日付変更",
            "日付が変わりました。\nタイトル画面へ戻ります。",
            string.Empty,
            () =>
            {
                ApplicationRebootor.Reboot();
            },
            false);
        }
    }
}

using GLOW.Core.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.MaintenanceDialog.Presentation.View;
using Newtonsoft.Json;
using WonderPlanet.OpenURLExtension;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.MaintenanceDialog.Presentation.Presenter
{
    public class MaintenanceDialogPresenter : IMaintenanceDialogViewDelegate
    {
        public class MaintenanceMessageContent
        {
            [JsonProperty("start_at")]
            public string StartAt;
            [JsonProperty("end_at")]
            public string EndAt;
            [JsonProperty("text")]
            public string Text;
        }

        [Inject] IAnnouncementViewFacade AnnouncementViewFacade { get; }
        [Inject] MaintenanceDialogViewController ViewController { get; }
        [Inject] MaintenanceDialogViewController.Argument Argument { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }

        void IMaintenanceDialogViewDelegate.ViewDidLoad()
        {
            var convertedMessage = ConvertMessage(Argument.MessageText);
            ViewController.SetMessage(convertedMessage);
        }

        void IMaintenanceDialogViewDelegate.OnOpenSNSPage()
        {
            CustomOpenURL.OpenURL(Credentials.OfficialSnsURL);
        }

        void IMaintenanceDialogViewDelegate.OnOpenAnnouncementView()
        {
            AnnouncementViewFacade.ShowMenuAnnouncement(ViewController);
        }

        void IMaintenanceDialogViewDelegate.OnClose()
        {
            ViewController.OnClose?.Invoke();
            ViewController.Dismiss(completion: () => ApplicationRebootor.Reboot());
        }

        string ConvertMessage(string message)
        {
            var content = JsonConvert.DeserializeObject<MaintenanceMessageContent>(message);

            return content.Text;
        }
    }
}

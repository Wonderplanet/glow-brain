using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.UseCases;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.HomeMenuSetting.Domain.UseCase;
using GLOW.Scenes.HomeMenuSetting.Presentation.Translator;
using GLOW.Scenes.HomeMenuSetting.Presentation.View;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.Presenter
{
    public class HomeMenuSettingPresenter : IHomeMenuSettingViewDelegate
    {
        [Inject] HomeMenuSettingViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ShowHomeMenuSettingUseCase ShowHomeMenuSettingUseCase { get; }
        [Inject] SwitchBgmGameOptionUseCase SwitchBgmGameOptionUseCase { get; }
        [Inject] SwitchSeGameOptionUseCase SwitchSeGameOptionUseCase { get; }
        [Inject] SwitchDamageDisplayGameOptionUseCase SwitchDamageDisplayGameOptionUseCase { get; }
        [Inject] SwitchPushOffGameOptionUseCase SwitchPushOffGameOptionUseCase { get; }
        [Inject] SetSpecialAttackCutInPlayTypeGameOptionUseCase SetSpecialAttackCutInPlayTypeGameOptionUseCase { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        
        
        public void OnViewWillAppear()
        {
            var homeMenuSettingModel = ShowHomeMenuSettingUseCase.GetHomeMenuSetting();
            var viewModel = HomeMenuSettingViewModelTranslator.ToHomeMenuSettingViewModel(homeMenuSettingModel);
            
            ViewController.Setup(viewModel);
        }

        public void OnBgmMuteToggleSwitched()
        {
            var isMute = SwitchBgmGameOptionUseCase.SwitchBgmGameOption();
            ViewController.SetBgmToggleOn(isMute);
        }

        public void OnSeMuteToggleSwitched()
        {
            var isMute = SwitchSeGameOptionUseCase.SwitchSeGameOption();
            ViewController.SetSeToggleOn(isMute); 
        }

        public void OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            var playType = SetSpecialAttackCutInPlayTypeGameOptionUseCase
                .SetSpecialAttackCutInPlayTypeGameOption(specialAttackCutInPlayType);
                
            ViewController.SetSpecialAttackCutInToggleOn(playType);
        }
        
        public void OnPushOffToggleSwitched()
        {
            var isOff = SwitchPushOffGameOptionUseCase.SwitchPushGameOption();

            if (isOff)
            {
                // ローカルプッシュを全て削除
                LocalNotificationScheduler.RemoveAllSchedules();
            }
            else
            {
                // ローカルプッシュの再設定
                LocalNotificationScheduler.RefreshAllSchedules();
            }
            
            ViewController.SetPushToggleOn(isOff);
        }

        public void OnDamageDisplayToggleSwitched()
        {
            var isDamageDisplay = SwitchDamageDisplayGameOptionUseCase.SwitchDamageDisplayGameOption();
            ViewController.SetDamageDisplayToggleOn(isDamageDisplay);
        }

        public void OnCloseSelected()
        {
            ViewController.Dismiss();
        }
    }
}
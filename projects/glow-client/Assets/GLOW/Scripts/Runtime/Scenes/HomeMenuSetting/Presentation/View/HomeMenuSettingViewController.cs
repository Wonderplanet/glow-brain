using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.HomeMenuSetting.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMenuSetting.Presentation.View
{
    public class HomeMenuSettingViewController : UIViewController<HomeMenuSettingView>, IEscapeResponder
    {
        [Inject] IHomeMenuSettingViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }
        
        public void Setup(HomeMenuSettingViewModel viewModel)
        {
            ActualView.SetBgmToggleOn(!viewModel.IsBgmMute);
            ActualView.SetSeToggleOn(!viewModel.IsSeMute);
            ActualView.SetSpecialAttackCutInToggleOn(viewModel.SpecialAttackCutInPlayType);
            ActualView.SetPushToggleOn(!viewModel.IsPushOff);
            ActualView.SetDamageDisplayToggleOn(viewModel.IsDamageDisplay);
            ActualView.SetAppVersionText(viewModel.AppVersion);
        }
        
        public void SetBgmToggleOn(BgmMuteFlag isMute)
        {
            ActualView.SetBgmToggleOn(!isMute);
        }
        
        public void SetSeToggleOn(SeMuteFlag isMute)
        {
            ActualView.SetSeToggleOn(!isMute);
        }

        public void SetSpecialAttackCutInToggleOn(SpecialAttackCutInPlayType specialAttackCutInPlayType)
        {
            ActualView.SetSpecialAttackCutInToggleOn(specialAttackCutInPlayType);
        }
        
        public void SetPushToggleOn(PushOffFlag isOff)
        {
            ActualView.SetPushToggleOn(!isOff);
        }

        public void SetDamageDisplayToggleOn(DamageDisplayFlag isDamageDisplay)
        {
            ActualView.SetDamageDisplayToggleOn(isDamageDisplay);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if(ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnCloseSelected();
            return true;
        }
        
        [UIAction]
        void OnBgmMuteToggleSelected()
        {
            ViewDelegate.OnBgmMuteToggleSwitched();
        }
        
        [UIAction]
        void OnSeMuteToggleSelected()
        {
            ViewDelegate.OnSeMuteToggleSwitched();
        }
        
        [UIAction]
        void OnSpecialAttackCutInOnSelected()
        {
            ViewDelegate.OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType.On);
        }
        
        [UIAction]
        void OnSpecialAttackCutInOffSelected()
        {
            ViewDelegate.OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType.Off);
        }
        
        [UIAction]
        void OnSpecialAttackCutInOnceADaySelected()
        {
            ViewDelegate.OnSpecialAttackCutInToggleSwitched(SpecialAttackCutInPlayType.OnceADay);
        }
        
        [UIAction]
        void OnPushOffToggleSelected()
        {
            ViewDelegate.OnPushOffToggleSwitched();
        }

        [UIAction]
        void OnDamageDisplayToggleSelected()
        {
            ViewDelegate.OnDamageDisplayToggleSwitched();
        }
        
        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
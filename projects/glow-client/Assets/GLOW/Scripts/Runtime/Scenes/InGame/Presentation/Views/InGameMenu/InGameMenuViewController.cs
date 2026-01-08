using System;
using DG.Tweening;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.GameOption.Domain.Constants;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views.InGameMenu
{
    public class InGameMenuViewController : UIViewController<InGameMenuView>, IEscapeResponder
    {
        public record Argument(ITimeScaleControlHandler TimeScaleControlHandler, Action<UIViewController> OnClose);

        [Inject] IInGameMenuViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IInGameViewDelegate InGameViewDelegate { get; }
        [Inject] IInGameMenuSettingUpdateControl InGameMenuSettingUpdateControl { get; }
        [Inject] Argument Arg { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.SetTitleBackViewHidden(true);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            // 親のBindタイミングの関係でViewDidAppear()でBindする
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(InGameMenuModel model)
        {
            ActualView.SetBgmToggleOn(!model.IsBgmMute);
            ActualView.SetSeToggleOn(!model.IsSeMute);
            ActualView.SetSpecialAttackCutInToggleOn(model.SpecialAttackCutInPlayType);
            ActualView.SetTwoRowDeckToggleOn(model.IsTwoRowDeck);
            ActualView.SetDamageDisplayToggleOn(model.IsDamageDisplay);
            ActualView.SetUpTitleBackViewAttention(model.InGameConsumptionType, model.IsInGameTypePvp);
            ActualView.SetGiveUpButton(model.CanGiveUp);
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
            InGameMenuSettingUpdateControl.SetSpecialAttackCutInPlayType(specialAttackCutInPlayType);
        }

        public void SetTwoRowDeckToggleOn(TwoRowDeckModeFlag isTwoRowDeck)
        {
            ActualView.SetTwoRowDeckToggleOn(isTwoRowDeck);
            InGameMenuSettingUpdateControl.SwitchDeckLayout();
        }

        public void SetDamageDisplayToggleOn(DamageDisplayFlag isDamageDisplay)
        {
            ActualView.SetDamageDisplayToggleOn(isDamageDisplay);
            InGameMenuSettingUpdateControl.SetDamageDisplay(isDamageDisplay);
        }

        void CloseAnimation()
        {
            Arg.OnClose?.Invoke(this);
            Dismiss();
        }

        bool IEscapeResponder.OnEscape()
        {
            if(ActualView.Hidden) return false;



            if (!ActualView.isTitleBackViewHidden)
            {
                ActualView.SetTitleBackViewHidden(true);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            }
            else
            {
                CloseAnimation();
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
            }

            return true;
        }

        public void TransitToHome()
        {
            // 閉じてからホームに戻る
            ActualView.SetTitleBackViewHidden(true);
            CloseAnimation();
            InGameViewDelegate.TransitToHome();
        }

        public void CloseView()
        {
            // 閉じる
            ActualView.SetTitleBackViewHidden(true);
            CloseAnimation();
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
        void OnDeckTwoRowToggleSelected()
        {
            ViewDelegate.OnTwoRowDeckToggleSwitched();
        }

        [UIAction]
        void OnDamageDisplayToggleSelected()
        {
            ViewDelegate.OnDamageDisplayToggleSwitched();
        }

        [UIAction]
        void OnTitleBackSelected()
        {
            ActualView.SetTitleBackViewHidden(false);
        }

        [UIAction]
        void OnTitleBackCancel()
        {
            ActualView.SetTitleBackViewHidden(true);
        }

        [UIAction]
        void OnClose()
        {
            CloseAnimation();
        }

        [UIAction]
        void OnTitleBackOkSelected()
        {
            Arg.TimeScaleControlHandler?.Dispose();
            ViewDelegate.Abort();
        }
    }
}

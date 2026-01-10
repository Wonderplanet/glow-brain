using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UserLevelUp.Presentation.View
{
    public class UserLevelUpViewController : UIViewController<UserLevelUpResultView>, IEscapeResponder
    {
        public record Argument(UserLevelUpResultViewModel ViewModel, Action OnViewClosed);

        [Inject] IUserLevelUpResultViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ActualView.OnPlayerResourceIconTapped = ViewDelegate.OnIconSelected;
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetupUserLevelNumber(UserLevel userLevel, bool isLevelMax = false)
        {
            ActualView.SetupUserLevelNumber(userLevel, isLevelMax);
        }

        public async UniTask PlayLevelUpEffectAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayLevelUpEffectAnimation(cancellationToken);
        }

        public void SkipLevelUpEffectAnimation()
        {
            ActualView.SkipAnimation();
        }

        public async UniTask PlayUserLevelLabel(CancellationToken cancellationToken)
        {
            await ActualView.PlayUserLevelLabel(cancellationToken);
        }

        public async UniTask PlayMaxStaminaUpLabel(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayMaxStaminaUpLabel(beforeMaxStamina, afterMaxStamina, cancellationToken);
        }

        public async UniTask PlayMaxStaminaDifference(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayMaxStaminaDifference(beforeMaxStamina, afterMaxStamina, cancellationToken);
        }

        public async UniTask PlayRewardLabelVisible(CancellationToken cancellationToken)
        {
            await ActualView.PlayRewardLabelVisible(cancellationToken);
        }

        public async UniTask PlayRewardItemAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> iconViewModels,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRewardItemAnimation(iconViewModels, cancellationToken);
        }

        public async UniTask PlayCloseTextVisible(CancellationToken cancellationToken)
        {
            await ActualView.PlayCloseTextVisible(cancellationToken);
        }

        public async UniTask PlayFadeOut(CancellationToken cancellationToken)
        {
            await ActualView.PlayFadeOut(cancellationToken);
        }

        public void ShowUserLevel()
        {
            ActualView.ShowUserLevel();
        }

        public void ShowMaxStaminaComponent(
            Stamina beforeMaxStamina,
            Stamina afterMaxStamina)
        {
            ActualView.ShowMaxStaminaComponent(beforeMaxStamina, afterMaxStamina);
        }

        public void ShowRewardLabel()
        {
            ActualView.ShowRewardLabel();
        }

        public void ShowRewardList(IReadOnlyList<PlayerResourceIconViewModel> iconViewModels)
        {
            ActualView.ShowRewardList(iconViewModels);
        }

        public void ShowCloseLabel()
        {
            ActualView.ShowCloseLabel();
        }

        public void ShowSkipButton()
        {
            ActualView.ShowSkipButton();
        }

        public void HideSkipButton()
        {
            ActualView.HideSkipButton();
        }

        public void ShowCloseButton()
        {
            ActualView.ShowCloseButton();
        }

        public void HideCloseButton()
        {
            ActualView.HideCloseButton();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            ViewDelegate.OnBackButton();
            return true;
        }

        [UIAction]
        void OnSkipTapped()
        {
            ViewDelegate.OnSkipSelected();
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}

using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.UnitLevelUpDialogView.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitLevelUpDialogView.Presentation.Views
{
    public class UnitLevelUpDialogViewController : UIViewController<UnitLevelUpDialogView>, IEscapeResponder
    {
        public record Argument(UserDataId UserUnitId, Action<bool> OnClose);

        [Inject] IUnitLevelUpDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ITutorialBackKeyViewDelegate TutorialBackKeyHandler { get; }

        UnitLevelUpDialogViewModel _viewModel;
        UnitLevel _selectLevel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Bind(this, ActualView);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void Setup(UnitLevelUpDialogViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(
                viewModel.IconViewModel,
                viewModel.CurrentLevel,
                viewModel.CurrentHp,
                viewModel.CurrentAttackPower,
                viewModel.RoleType);
            SetSelectLevel(GetMinimumLevel());
        }
        
        public void PlayResourceAppearanceAnimation()
        {
            ActualView.PlayResourceAppearanceAnimation();
        }

        void SetSelectLevel(UnitLevel level)
        {
            _selectLevel = level;
            var levelViewModel = _viewModel.LevelValues.Find(lv => lv.Level == level);

            ActualView.SetSelectLevel(levelViewModel, _viewModel.PossessionCoin);
        }

        UnitLevel GetMinimumLevel()
        {
            return _viewModel.LevelValues
                .Where(lv => !lv.ButtonState.EnableMinus)
                .Min(lv => lv.Level);
        }

        UnitLevel GetMaximumLevel()
        {
            return _viewModel.LevelValues
                .Where(lv => !lv.ButtonState.EnableMaximum)
                .Min(lv => lv.Level);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;
            
            // チュートリアル中はバックキーを無効化
            if (TutorialBackKeyHandler.IsPlayingTutorial())
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            ViewDelegate.OnCloseButtonTapped();
            return true;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }

        [UIAction]
        void OnEnhanceButtonTapped()
        {
            ViewDelegate.OnEnhanceButtonTapped(_selectLevel);
        }

        [UIAction]
        void OnLevelIncrementButtonTapped()
        {
            SetSelectLevel(_selectLevel + 1);
        }

        [UIAction]
        void OnLevelDecrementButtonTapped()
        {
            SetSelectLevel(_selectLevel - 1);
        }

        [UIAction]
        void OnLevelMinimumButtonTapped()
        {
            SetSelectLevel(GetMinimumLevel());
        }

        [UIAction]
        void OnLevelMaximumButtonTapped()
        {
            SetSelectLevel(GetMaximumLevel());
        }

        [UIAction]
        void OnRankUpDetailButtonTapped()
        {
            ViewDelegate.OnRankUpDetailButtonTapped();
        }
    }
}

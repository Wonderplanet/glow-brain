using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Extensions;
using GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.OutpostEnhanceLevelUpDialog.Presentation.Views
{
    public class OutpostEnhanceLevelUpDialogViewController : UIViewController<OutpostEnhanceLevelUpDialogView>
    {
        public record Argument(MasterDataId MstEnhanceId ,Action<bool, OutpostEnhanceLevel> OnClose);

        [Inject] IOutpostEnhanceLevelUpDialogViewDelegate ViewDelegate { get; }

        OutpostEnhanceLevelUpDialogViewModel _viewModel;
        OutpostEnhanceLevel _selectLevel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(OutpostEnhanceLevelUpDialogViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel.CurrentLevel, viewModel.PossessionCoin);
            SetSelectLevel(GetMinimumLevel());
        }

        void SetSelectLevel(OutpostEnhanceLevel level)
        {
            _selectLevel = level;
            var levelViewModel = _viewModel.LevelValues.Find(lv => lv.Level == level);

            ActualView.SetSelectLevel(levelViewModel);
        }

        OutpostEnhanceLevel GetMinimumLevel()
        {
            return _viewModel.LevelValues
                .Where(lv => !lv.ButtonState.EnableMinus)
                .Min(lv => lv.Level);
        }

        OutpostEnhanceLevel GetMaximumLevel()
        {
            return _viewModel.LevelValues
                .Where(lv => !lv.ButtonState.EnableMaximum)
                .Min(lv => lv.Level);
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
    }
}

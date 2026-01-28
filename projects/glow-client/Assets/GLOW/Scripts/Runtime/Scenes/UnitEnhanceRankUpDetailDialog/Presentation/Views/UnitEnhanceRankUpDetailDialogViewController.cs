using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views
{
    public class UnitEnhanceRankUpDetailDialogViewController : UIViewController<UnitEnhanceRankUpDetailDialogView>
    {
        [Inject] IUnitEnhanceRankUpDetailDialogViewDelegate ViewDelegate { get; }

        public record Argument(UserDataId UserUnitId);

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void Setup(UnitEnhanceRankUpDetailDialogViewModel viewModel, Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            ActualView.Setup(viewModel, onItemTapped);
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}

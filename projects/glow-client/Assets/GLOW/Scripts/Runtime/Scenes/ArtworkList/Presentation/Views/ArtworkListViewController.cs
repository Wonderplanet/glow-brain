using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFormation.Presentation.Views.Components;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ArtworkList.Presentation.Views
{
    public class ArtworkListViewController :
        UIViewController<ArtworkListView>,
        IArtworkFormationListComponentDelegate
    {
        [Inject] IArtworkListViewDelegate ViewDelegate { get; set; }

        public override void ViewWillAppear(bool animated)
        {
            ViewDelegate.OnViewWillAppear();
            base.ViewWillAppear(animated);
        }


        public void SetUp(ArtworkFormationListViewModel viewModel)
        {
            ActualView.SetUp(viewModel, this);
        }

        void IArtworkFormationListComponentDelegate.OnListCellTapped(
            MasterDataId mstArtworkId,
            ArtworkCompleteFlag flag,
            ArtworkFormationListCell cell)
        {
            // タップ音を再生
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

            ViewDelegate.OnListCellTapped(mstArtworkId);
        }

        void IArtworkFormationListComponentDelegate.OnListCellLongTapped(
            MasterDataId mstArtworkId,
            List<ArtworkFormationListCellViewModel> cellViewModels)
        {
            // 一覧画面での長押しでは何もしない
        }

        [UIAction]
        void OnSortAndFilterButtonTapped()
        {
            ViewDelegate.OnSortAndFilterButtonTapped();
        }

        [UIAction]
        void OnSortButtonTapped()
        {
            ViewDelegate.OnSortButtonTapped();
        }
    }
}


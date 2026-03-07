using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFormation.Presentation.Views.Components;
using UIKit;
using Zenject;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views
{
    public class ArtworkFormationViewController :
        UIViewController<ArtworkFormationView>,
        IArtworkFormationListComponentDelegate,
        IArtworkFormationPartyComponentDelegate
    {
        [Inject] IArtworkFormationViewDelegate ViewDelegate { get; set; }

        public override void ViewDidLoad()
        {
            ViewDelegate.OnViewDidLoad();
            base.ViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            ViewDelegate.OnViewWillAppear();
            base.ViewWillAppear(animated);
        }

        public override void ViewWillDisappear(bool animated)
        {
            // 編成の更新のため
            ViewDelegate.OnViewWillDisappear();
            base.ViewWillDisappear(animated);
        }

        public void SetUp(ArtworkFormationViewModel viewModel)
        {
            ActualView.InitializeView(this, this);
            ActualView.SetUp(viewModel);
        }

        public void UpdateArtworkList(ArtworkFormationListViewModel listViewModel)
        {
            ActualView.UpdateListComponent(listViewModel);
        }

        public void UpdateArtworkPartyView()
        {
            var partyViewModel = ViewDelegate.GetPartyViewModel();
            ActualView.UpdatePartyComponent(partyViewModel);
        }

        void IArtworkFormationPartyComponentDelegate.OnPartyCellTapped(MasterDataId mstArtworkId)
        {
            // 最後の1つを外そうとした場合は警告を表示
            if (IsRemovingLastArtwork())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                ViewDelegate.OnTapRemoveLastArtwork(mstArtworkId);
                return;
            }

            // Presenterの編成状態を切り替え
            ViewDelegate.OnArtworkAssignmentToggled(mstArtworkId, true);

            // タップ音を再生（編成内を直接タップして外す）
            SoundEffectPlayer.Play(SoundEffectId.SSE_031_006);

            // パーティ表示の更新
            UpdateArtworkPartyView();

            // リスト表示の更新
            UpdateArtworkListView();
        }

        void IArtworkFormationListComponentDelegate.OnListCellTapped(
            MasterDataId mstArtworkId,
            ArtworkCompleteFlag flag,
            ArtworkFormationListCell cell)
        {
            if (!flag)
            {
                // 未完成の原画の場合
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                ViewDelegate.OnIncompleteArtworkTapped(mstArtworkId);
                return;
            }
            
            // 現在の編成状態を取得
            var isAssigned = ViewDelegate.GetAssignedArtworkIds().Any(id => id == mstArtworkId);
            
            // タップしたセルが未編成で編成数が最大の場合
            if (!isAssigned && IsPartyFull())
            {
                ViewDelegate.OnArtworkTappedWhenPartyFull(mstArtworkId);
                return;
            }
            
            // タップしたセルが編成済みで外す場合で最後の1つの場合
            if (isAssigned && IsRemovingLastArtwork())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                ViewDelegate.OnTapRemoveLastArtwork(mstArtworkId);
                return;
            }
            
            // 通常の編成追加/削除処理
            HandleNormalArtworkTap(mstArtworkId, isAssigned, cell);
        }
        
        void IArtworkFormationListComponentDelegate.OnListCellLongTapped(
            MasterDataId mstArtworkId,
            List<ArtworkFormationListCellViewModel> cellViewModels)
        {
            ViewDelegate.OnListCellLongTapped(mstArtworkId, cellViewModels);
        }

        void HandleNormalArtworkTap(
            MasterDataId mstArtworkId,
            bool isAssigned,
            ArtworkFormationListCell cell)
        {
            // 現在の編成数を取得
            var currentAssignedCount = ViewDelegate.GetAssignedArtworkIds().Count;

            // Presenterに編成追加/削除の処理を依頼
            ViewDelegate.OnArtworkAssignmentToggled(mstArtworkId, isAssigned);

            // タップ音を再生
            PlayAssignmentSound(isAssigned);

            // UIを更新
            UpdateArtworkPartyView();

            // 編成数が9→10または10→9になる場合は、全セルのグレーアウト状態を更新
            var newAssignedCount = ViewDelegate.GetAssignedArtworkIds().Count;
            var crossedThreshold = (currentAssignedCount == 9 && newAssignedCount == 10) ||
                                   (currentAssignedCount == 10 && newAssignedCount == 9);

            if (crossedThreshold)
            {
                // リスト全体を更新してグレーアウト状態を反映
                UpdateArtworkListView();
            }
            else
            {
                // 該当セルのみ更新
                UpdateArtworkListCell(mstArtworkId, cell);
            }
        }

        bool IsPartyFull()
        {
            var assignedArtworkIds = ViewDelegate.GetAssignedArtworkIds();
            return assignedArtworkIds.Count >= 10;
        }

        bool IsRemovingLastArtwork()
        {
            var assignedArtworkIds = ViewDelegate.GetAssignedArtworkIds();
            return assignedArtworkIds.Count <= 1;
        }

        void PlayAssignmentSound(bool isAssigned)
        {
            if (isAssigned)
            {
                // 編成から外す
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            }
            else
            {
                // 編成に追加
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_001);
            }
        }
        

        void UpdateArtworkListView()
        {
            var listViewModel = ViewDelegate.GetListViewModel();
            ActualView.UpdateListComponent(listViewModel);
        }

        void UpdateArtworkListCell(MasterDataId mstArtworkId, ArtworkFormationListCell cell = null)
        {
            var cellViewModel = ViewDelegate.GetListCellViewModel(mstArtworkId);
            ActualView.UpdateListCellAssignment(mstArtworkId, cellViewModel, cell);
        }

        [UIAction]
        void OnRecommendButton()
        {
            ViewDelegate.OnRecommendButtonTapped();
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

        [UIAction]
        void OnHelpButtonTapped()
        {
            ViewDelegate.OnHelpButtonTapped();
        }

    }
}


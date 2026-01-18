using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.FragmentProvisionRatio.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.FragmentProvisionRatio.Presentation
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-5_ランダムかけらBOX提供割合ダイアログ
    /// </summary>
    public class FragmentProvisionRatioViewController : UIViewController<FragmentProvisionRatioView>, IEscapeResponder
    {
        public record Argument(MasterDataId MstFragmentBoxGroupId, MasterDataId RandomFragmentBoxMstItemId);

        [Inject] IFragmentProvisionRatioViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        static readonly int Disappear = Animator.StringToHash("disappear");
        static readonly int Appear = Animator.StringToHash("appear");

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this,ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ActualView.Animator.SetTrigger(Appear);
            ActualView.RootRect.verticalNormalizedPosition = 1f;
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillAppear(animated);
            ActualView.Animator.SetTrigger(Disappear);
        }

        public void SetViewModel(FragmentProvisionRatioViewModel viewModel)
        {
            // レア度別確率
            ActualView.RatioRarityContent.gameObject.SetActive(!viewModel.IsSingleRarity);

            ActualView.RatioRarityContent.RarityR.rootObject.SetActive(
                viewModel.ListViewModels.Exists(l => l.Rarity == Rarity.R));
            ActualView.RatioRarityContent.RaritySR.rootObject.SetActive(
                viewModel.ListViewModels.Exists(l => l.Rarity == Rarity.SR));
            ActualView.RatioRarityContent.RaritySSR.rootObject.SetActive(
                viewModel.ListViewModels.Exists(l => l.Rarity == Rarity.SSR));
            ActualView.RatioRarityContent.RarityUR.rootObject.SetActive(
                viewModel.ListViewModels.Exists(l => l.Rarity == Rarity.UR));

            foreach (var list in viewModel.ListViewModels)
            {
                ActualView.RatioRarityContent.SetModel(list.Rarity, list.RatioByRarity);
            }

            // コンテンツ表示
            ActualView.FragmentProvisionRatioLineUpViewR.gameObject.SetActive(viewModel.ExistR);
            ActualView.FragmentProvisionRatioLineUpViewSR.gameObject.SetActive(viewModel.ExistSR);
            ActualView.FragmentProvisionRatioLineUpViewSSR.gameObject.SetActive(viewModel.ExistSSR);
            ActualView.FragmentProvisionRatioLineUpViewUR.gameObject.SetActive(viewModel.ExistUR);

            // 何か使う用途あれば変数に入れる
            FragmentProvisionRatioLineUpCreator creator = null;
            if (viewModel.ExistR)
                creator = new FragmentProvisionRatioLineUpCreator(
                    ActualView.FragmentProvisionRatioLineUpViewR,
                    viewModel.ListViewModels.First(vm => vm.Rarity == Rarity.R),
                    OnShowUnitView);
            if (viewModel.ExistSR)
                creator = new FragmentProvisionRatioLineUpCreator(
                    ActualView.FragmentProvisionRatioLineUpViewSR,
                    viewModel.ListViewModels.First(vm => vm.Rarity == Rarity.SR),
                    OnShowUnitView);
            if (viewModel.ExistSSR)
                creator = new FragmentProvisionRatioLineUpCreator(
                    ActualView.FragmentProvisionRatioLineUpViewSSR,
                    viewModel.ListViewModels.First(vm => vm.Rarity == Rarity.SSR),
                    OnShowUnitView);
            if (viewModel.ExistUR)
                creator = new FragmentProvisionRatioLineUpCreator(
                    ActualView.FragmentProvisionRatioLineUpViewUR,
                    viewModel.ListViewModels.First(vm => vm.Rarity == Rarity.UR),
                    OnShowUnitView);

            // 獲得先遷移
            ActualView.WhereGetMessageAreaComponent.Hidden = !viewModel.IsAvailableLocation();
            if (viewModel.IsAvailableLocation())
            {
                ActualView.WhereGetMessageAreaComponent.InitializeView();
                ActualView.WhereGetMessageAreaComponent.EarnLocationSetActive(
                    viewModel.AvailableLocation.EarnLocationViewModel1, OnTransitionButtonTapped);
                ActualView.WhereGetMessageAreaComponent.EarnLocationSetActive(
                    viewModel.AvailableLocation.EarnLocationViewModel2, OnTransitionButtonTapped);
            }

            creator?.Setup();
        }

        void OnTransitionButtonTapped(ItemDetailEarnLocationViewModel earnLocationViewModel, bool popBeforeDetail)
        {
            ViewDelegate.OnTransitionButtonTapped(earnLocationViewModel);
        }


        void OnShowUnitView(MasterDataId mstUnitId)
        {
            ViewDelegate.OnShowUnitView(mstUnitId);
        }

        [UIAction]
        void OnTransitShop()
        {
            ViewDelegate.OnTransitShop();
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnClose();
            return true;
        }
    }

}

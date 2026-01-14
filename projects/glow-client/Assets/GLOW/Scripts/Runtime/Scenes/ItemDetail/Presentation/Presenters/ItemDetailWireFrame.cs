using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AppAppliedBalanceDialog.Presentation;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.EmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.Views;
using GLOW.Scenes.ItemDetail.Domain.Models;
using GLOW.Scenes.ItemDetail.Domain.UseCase;
using GLOW.Scenes.ItemDetail.Presentation.Translator;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using GLOW.Scenes.UnitDetailModal.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Presentation.Presenters
{
    public interface IItemDetailWireFrame
    {
        void ShowItemDetailView(ResourceType type, MasterDataId id, PlayerResourceAmount amount, UIViewController viewController, bool popBeforeDetail = false);
        void ShowNoTransitionLayoutItemDetailView(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController,
             bool popBeforeDetail = false);
        void ShowItemDetailView(PlayerResourceIconViewModel viewModel, UIViewController viewController);
        void ShowNoTransitionLayoutItemDetailView(PlayerResourceIconViewModel viewModel, UIViewController viewController);
        void ShowNoTransitionLayoutItemDetailView(
            PlayerResourceIconViewModel viewModel,
            UIViewController viewController,
            MaxStatusFlag maxStatusFlag);
        void ShowMissionBonusPointDetail(MasterDataId masterDataId, UIViewController viewController, bool popBeforeDetail = false);
        void ShowGachaCostItemDetailView(MasterDataId mstCostId, UIViewController viewController);
    }

    public class ItemDetailWireFrame : IItemDetailWireFrame
    {
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] ShowItemDetailUseCase ShowItemDetailUseCase { get; }

        void IItemDetailWireFrame.ShowItemDetailView(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController,
            bool popBeforeDetail = false)
        {
            ShowItemDetailFromResourceDetailViewModel(type, id, amount, viewController,ShowTransitAreaFlag.True, popBeforeDetail);

        }
        void IItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController,
            bool popBeforeDetail = false)
        {
            ShowItemDetailFromResourceDetailViewModel(type, id, amount, viewController, ShowTransitAreaFlag.False, popBeforeDetail);
        }

        // viewControllerはInjectで処理しても良いかも
        // controller使いたいときはUIViewContoller返す
        void IItemDetailWireFrame.ShowItemDetailView(PlayerResourceIconViewModel viewModel, UIViewController viewController)
        {
            ShowItemDetailFromResourceIconViewModel(viewModel, viewController, ShowTransitAreaFlag.True, MaxStatusFlag.True);
        }

        void IItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
            PlayerResourceIconViewModel viewModel,
            UIViewController viewController)
        {
            ShowItemDetailFromResourceIconViewModel(viewModel, viewController, ShowTransitAreaFlag.False, MaxStatusFlag.True);
        }

        void IItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(
            PlayerResourceIconViewModel viewModel,
            UIViewController viewController,
            MaxStatusFlag maxStatusFlag)
        {
            ShowItemDetailFromResourceIconViewModel(viewModel, viewController, ShowTransitAreaFlag.False, maxStatusFlag);
        }

        void IItemDetailWireFrame.ShowMissionBonusPointDetail(MasterDataId masterDataId, UIViewController viewController, bool popBeforeDetail = false)
        {
            ShowItemDetail(
                ResourceType.MissionBonusPoint,
                masterDataId,
                PlayerResourceAmount.Empty,
                viewController,
                ShowTransitAreaFlag.False,
                popBeforeDetail);
        }

        void IItemDetailWireFrame.ShowGachaCostItemDetailView(MasterDataId mstCostId, UIViewController viewController)
        {
            ShowGachaCostItemDetailView(mstCostId, viewController, ShowTransitAreaFlag.True);
        }

        void ShowItemDetailFromResourceIconViewModel(PlayerResourceIconViewModel viewModel,
            UIViewController viewController, ShowTransitAreaFlag transitButtons, MaxStatusFlag unitLevelMaxFlag)
        {
            switch (viewModel.ResourceType)
            {
                case ResourceType.Item:
                case ResourceType.Coin:
                case ResourceType.Exp:
                case ResourceType.MissionBonusPoint:
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                    ShowItemDetail(viewModel, viewController,transitButtons);
                    break;
                case ResourceType.ArtworkFragment:
                    // 図鑑の詳細に遷移させるためここでは処理を行わない
                    break;
                case ResourceType.Emblem:
                    ShowEmblemDetail(viewModel.Id, viewController);
                    break;
                case ResourceType.Unit:
                    ShowUnitDetailModal(viewModel.Id, viewController, unitLevelMaxFlag);
                    break;
                case ResourceType.Artwork:
                    ShowArtworkDetail(viewModel.Id, viewController);
                    break;
            }
        }


        void ShowItemDetailFromResourceDetailViewModel(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController,
            ShowTransitAreaFlag transitButtons,
            bool popBeforeDetail = false)
        {
            switch (type)
            {
                case ResourceType.Item:
                case ResourceType.Coin:
                case ResourceType.Exp:
                case ResourceType.IdleCoin:
                case ResourceType.MissionBonusPoint:
                case ResourceType.FreeDiamond:
                case ResourceType.PaidDiamond:
                    ShowItemDetail(type, id, amount, viewController, transitButtons, popBeforeDetail);
                    break;
                case ResourceType.ArtworkFragment:
                    // 図鑑の詳細に遷移させるためここでは処理を行わない
                    break;
                case ResourceType.Emblem:
                    ShowEmblemDetail(id, viewController);
                    break;
                case ResourceType.Unit:
                    ShowUnitDetailModal(id, viewController, MaxStatusFlag.True);
                    break;
                case ResourceType.Artwork:
                    ShowArtworkDetail(id, viewController);
                    break;
            }
        }
        void ShowItemDetail(
            PlayerResourceIconViewModel viewModel,
            UIViewController viewController,
            ShowTransitAreaFlag showTransitAreaFlag,
            bool popBeforeDetail = false)
        {
            var itemDetailModel =
                ShowItemDetailUseCase.GetItemDetail(viewModel.ResourceType, viewModel.Id, viewModel.Amount);
            if (itemDetailModel.IsEmpty()) return;

            var itemDetailViewModel =
                ItemDetailWithTransitViewModelTranslator.ToItemDetailWithTransitViewModel(itemDetailModel);
            var argument = new ItemDetailViewController.Argument(itemDetailViewModel, showTransitAreaFlag, popBeforeDetail);

            var controller = ViewFactory.Create<ItemDetailViewController, ItemDetailViewController.Argument>(argument);
            viewController.PresentModally(controller);
        }

        void ShowItemDetail(
            ResourceType type,
            MasterDataId id,
            PlayerResourceAmount amount,
            UIViewController viewController,
            ShowTransitAreaFlag showTransitAreaFlag,
            bool popBeforeDetail = false)
        {
            var itemDetailModel = ShowItemDetailUseCase.GetItemDetail(
                type,
                id,
                amount);

            if (itemDetailModel.IsEmpty()) return;

            var itemDetailViewModel =
                ItemDetailWithTransitViewModelTranslator.ToItemDetailWithTransitViewModel(itemDetailModel);
            var argument = new ItemDetailViewController.Argument(itemDetailViewModel, showTransitAreaFlag, popBeforeDetail);

            var controller = ViewFactory.Create<ItemDetailViewController, ItemDetailViewController.Argument>(argument);
            viewController.PresentModally(controller);
        }

        void ShowArtworkDetail(MasterDataId masterDataId, UIViewController viewController)
        {
            var argument = new ArtworkExpandDialogViewController.Argument(
                masterDataId, ArtworkDetailDisplayType.GrayOut);
            var controller = ViewFactory.Create<ArtworkExpandDialogViewController,
                ArtworkExpandDialogViewController.Argument>(argument);

            viewController.PresentModally(controller);
        }

        void ShowEmblemDetail(MasterDataId masterDataId, UIViewController viewController)
        {
            var argument = new EmblemDetailViewController.Argument(masterDataId);
            var vc = ViewFactory.Create<
                EmblemDetailViewController,
                EmblemDetailViewController.Argument>(argument);
            viewController.PresentModally(vc);
        }

        void ShowUnitDetailModal(MasterDataId masterDataId, UIViewController viewController, MaxStatusFlag maxStatusFlag)
        {
            var argument = new UnitDetailModalViewController.Argument(masterDataId, maxStatusFlag);
            var vc = ViewFactory.Create<UnitDetailModalViewController, UnitDetailModalViewController.Argument>(argument);
            viewController.PresentModally(vc);
        }

        void ShowAppAppliedBalance(UIViewController viewController)
        {
            var appliedBalanceViewController = ViewFactory.Create<AppAppliedBalanceDialogViewController>();
            viewController.PresentModally(appliedBalanceViewController);
        }

        void ShowGachaCostItemDetailView(
            MasterDataId mstCostId,
            UIViewController viewController,
            ShowTransitAreaFlag showTransitAreaFlag)
        {
            var argument = new GachaCostItemDetailViewController.Argument(mstCostId, showTransitAreaFlag);
            var vc = ViewFactory.Create<GachaCostItemDetailViewController, GachaCostItemDetailViewController.Argument>(argument);
            viewController.PresentModally(vc);
        }
    }
}


using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Domain.UseCases;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Modules.InAppReview.Presentation;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.GachaConfirm.Presentation.Views;
using GLOW.Scenes.GachaLineupDialog.Domain.UseCases;
using GLOW.Scenes.GachaLineupDialog.Presentation.Translator;
using GLOW.Scenes.GachaList.Domain.UseCases;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaRatio.Domain.Model;
using GLOW.Scenes.GachaResult.Domain.UseCases;
using GLOW.Scenes.GachaResult.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using GLOW.Scenes.TutorialGachaReDrawDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.GachaResult.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-4_ガシャ結果
    /// </summary>
    public class GachaResultPresenter : IGachaResultViewDelegate
    {
        [Inject] GachaResultUseCase GachaResultUseCase { get; }
        [Inject] TutorialGachaResultConfirmUseCase TutorialGachaResultConfirmUseCase { get; }
        [Inject] TutorialGachaReDrawUseCase TutorialGachaReDrawUseCase { get; }
        [Inject] GachaResultViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IGachaDrawControl GachaDrawControl { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] GachaAnimationUnloadUseCase GachaAnimationUnloadUseCase { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IInAppReviewWireFrame InAppReviewWireFrame { get; }
        [Inject] GachaLineupDialogUseCase GachaLineupDialogUseCase { get; }
        [Inject] GachaWireFrame.Presentation.Presenters.GachaWireFrame GachaWireFrame { get; }
        [Inject] GetCommonReceiveItemUseCase GetCommonReceiveItemUseCase { get; }

        InAppReviewFlag _isInAppReviewDisplay = InAppReviewFlag.False;

        public void OnViewDidLoad()
        {
            var useCaseModel = GachaResultUseCase.GetGachaResultUseCase();
            var cellViewModels = new List<GachaResultCellViewModel>();
            var preConversionCellViewModels = new List<PlayerResourceIconViewModel>();

            ViewController.GachaId = useCaseModel.GachaDrawInfoModel.GachaId;
            ViewController.GachaDrawType = useCaseModel.GachaDrawInfoModel.GachaDrawType;

            _isInAppReviewDisplay = useCaseModel.IsAppReviewDisplay;

            GachaDrawControl.UpdateView(useCaseModel.GachaDrawInfoModel.GachaId);

            ViewController.SetIsGachaReDrawable(useCaseModel.DrawableFlag);

            foreach (var model in useCaseModel.GachaResultModels)
            {
                var cellViewModel = new GachaResultCellViewModel(
                    PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.PlayerResourceModel),
                    model.IsNewUnitBadge
                    );
                cellViewModels.Add(cellViewModel);
            }

            foreach (var model in useCaseModel.GachaResultConvertedModels)
            {
                var cellViewModel = PlayerResourceIconViewModel.Empty;
                if (!model.IsEmpty())
                {
                    cellViewModel = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(model.PlayerResourceModel);
                }
                preConversionCellViewModels.Add(cellViewModel);
            }

            var avatarViewModels = new List<PlayerResourceIconViewModel>();
            foreach (var model in useCaseModel.GachaResultAvatarModels)
            {
                // リーダーアバターのアセットパス表示のためのviewModel作成
                var avatarViewModel = new PlayerResourceIconViewModel
                (
                    new MasterDataId(""),
                    ResourceType.Item,
                    model.CharacterIconAssetPath.ToPlayerResourceIconAssetPath(),
                    IconRarityFrameType.Unit,
                    Rarity.R,
                    PlayerResourceAmount.Empty,
                    PlayerResourceAcquiredFlag.False,
                    StageClearTime.Empty,
                    RewardCategoryLabel.None
                );

                avatarViewModels.Add(avatarViewModel);
            }

            var viewModel = new GachaResultViewModel(
                useCaseModel.GachaDrawInfoModel.GachaType,
                cellViewModels,
                preConversionCellViewModels,
                avatarViewModels,
                useCaseModel.ExistsPreConversionResource);

            ViewController.SetupGachaResultView(viewModel);
        }

        void IGachaResultViewDelegate.OnReDrawButtonTapped(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            var argument = new GachaConfirmDialogViewController.Argument(gachaId,gachaDrawType);
            var controller = ViewFactory.Create<GachaConfirmDialogViewController, GachaConfirmDialogViewController.Argument>(argument);
            // ガチャ結果から再度引く場合のフラグをセットする
            controller.IsReDraw = true;
            ViewController.PresentModally(controller);
        }

        void IGachaResultViewDelegate.OnIconCellTapped(PlayerResourceIconViewModel model)
        {
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(model, ViewController, MaxStatusFlag.False);
        }

        void IGachaResultViewDelegate.ExitGachaResult()
        {
            // ホームBGMを再生
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_home);

            // ガシャBGMのアンロード
            GachaAnimationUnloadUseCase.UnloadGachaAnimAsset();
        }

        void IGachaResultViewDelegate.OnTutorialConfirmButtonTapped()
        {

            MessageViewUtil.ShowMessageWith2Buttons(
                "確認",
                "ガシャ結果を確定してキャラを\n獲得しますか？",
                "確定すると「UR1体確定チュートリアルガシャ」は引けなくなります。",
                "確定",
                "キャンセル",
                () =>
                {
                    DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async ct =>
                    {
                        // チュートリアルの進行と確定のAPI送信
                        await TutorialGachaResultConfirmUseCase.ConfirmTutorialGachaResult(ct);

                        // 引いたキャラを編成に加える ヘッダー更新
                        HomeHeaderDelegate.UpdateStatus();

                        // ガシャ結果画面を閉じる
                        ViewController.CloseGachaResult();
                    });
                },
                () => { }); // ダイアログを閉じる

        }

        void IGachaResultViewDelegate.OnTutorialReDrawButtonTapped(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            ShowTutorialGachaConfirmDialog(gachaId);
        }

        void IGachaResultViewDelegate.ShowInAppReview()
        {
            if (_isInAppReviewDisplay)
            {
                InAppReviewWireFrame.RequestStoreReview(() => {});
            }
        }

        void OnClickIconDetail(GachaRatioResourceModel resourceModel)
        {
            var playerResourceModel = GetCommonReceiveItemUseCase.GetPlayerResource(
                resourceModel.ResourceType,
                resourceModel.MasterDataId,
                resourceModel.Amount
            );

            GachaWireFrame.OnClickIconDetail(playerResourceModel, ViewController);
        }

        void ReDrawTutorialGacha()
        {
            GachaDrawControl.DrawTutorialGacha(true);
        }

        void ShowLineupDialog(MasterDataId gachaId)
        {
            // ガシャのラインナップを表示する
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async ct =>
            {
                var useCaseModel = await GachaLineupDialogUseCase.GetGachaLineupUseCaseModel(ct, gachaId);
                var viewModel =
                    GachaLineupDialogViewModelTranslator.TranslateToViewModel(useCaseModel, OnClickIconDetail);

                GachaWireFrame.OnGachaLineUpCloseAction = () => ShowTutorialGachaConfirmDialog(gachaId);
                GachaWireFrame.ShowGachaLineUpDialogView(gachaId, viewModel, ViewController);
            });
        }

        void ShowTutorialGachaConfirmDialog(MasterDataId gachaId)
        {
            var argument =
                new TutorialGachaReDrawDialogViewController.Argument(
                    ReDrawTutorialGacha,
                    () => ShowLineupDialog(gachaId));

            var controller = ViewFactory.Create<
                TutorialGachaReDrawDialogViewController,
                TutorialGachaReDrawDialogViewController.Argument>(argument);

            ViewController.PresentModally(controller);
        }
    }
}

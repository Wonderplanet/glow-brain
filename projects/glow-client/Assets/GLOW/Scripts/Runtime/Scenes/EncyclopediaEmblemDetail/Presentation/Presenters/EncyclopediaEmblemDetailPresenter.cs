using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.EmblemDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.UserEmblem.Domain.UseCases;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEmblemDetail.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-5_作品別エンブレム表示
    /// 　　91-5-1エンブレム詳細ダイアログ
    /// </summary>
    public class EncyclopediaEmblemDetailPresenter : IEncyclopediaEmblemDetailViewDelegate
    {
        [Inject] EncyclopediaEmblemDetailViewController ViewController { get; }
        [Inject] EncyclopediaEmblemDetailViewController.Argument Argument { get; }
        [Inject] GetEmblemDetailUseCase GetEmblemDetailUseCase { get; }
        [Inject] ApplyUserEmblemUseCase ApplyUserEmblemUseCase { get; }
        [Inject] ReceiveEncyclopediaFirstCollectionRewardUseCase ReceiveEncyclopediaFirstCollectionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        public void OnViewDidLoad()
        {
            var model = GetEmblemDetailUseCase.GetEmblemDetail(Argument.MstEmblemId);
            var viewModel = new EncyclopediaEmblemDetailViewModel(
                model.IconAssetPath,
                model.Name,
                model.Description
            );

            ViewController.Setup(viewModel);

            ReceiveFirstCollectionReward(Argument.MstEmblemId);
        }

        void ReceiveFirstCollectionReward(MasterDataId mstEmblemId)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                var results = await ReceiveEncyclopediaFirstCollectionRewardUseCase.ReceiveReward(
                    cancellationToken,
                    mstEmblemId,
                    EncyclopediaType.Emblem
                );

                if (results.Count <= 0) return;

                var viewModels = results
                    .Select(r =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();
                CommonReceiveWireFrame.Show(viewModels);
                HomeHeaderDelegate.UpdateStatus();
            });
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }

        public void OnAssignEmblemButtonTapped()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ApplyUserEmblemUseCase.ApplyUserEmblem(cancellationToken, Argument.MstEmblemId);
                HomeHeaderDelegate.UpdateBadgeStatus();
                HomeHeaderDelegate.UpdateStatus();
                ViewController.Dismiss();
            });
        }
    }
}

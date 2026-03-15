using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaUnitDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaUnitSpecialAttack.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaUnitDetail.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-1_ヒーローキャラ表示
    /// </summary>
    public class EncyclopediaUnitDetailPresenter : IEncyclopediaUnitDetailViewDelegate
    {
        [Inject] EncyclopediaUnitDetailViewController ViewController { get; }
        [Inject] HomeViewController HomeViewController { get; }
        [Inject] EncyclopediaUnitDetailViewController.Argument Argument { get; }
        [Inject] GetEncyclopediaUnitDetailUseCase GetEncyclopediaUnitDetailUseCase { get; }
        [Inject] ReceiveEncyclopediaFirstCollectionRewardUseCase ReceiveEncyclopediaFirstCollectionRewardUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        MasterDataId _selectedMstUnitId;

        public void OnViewDidLoad()
        {
            UpdateUnitInfo(Argument.SelectedMstUnitId);
        }

        void IEncyclopediaUnitDetailViewDelegate.OnSwitchUnit(MasterDataId mstUnitId)
        {
            UpdateUnitInfo(mstUnitId);
        }

        void IEncyclopediaUnitDetailViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void IEncyclopediaUnitDetailViewDelegate.OnPlaySpecialAttackButtonTapped()
        {
            var argument = new UnitSpecialAttackPreviewViewController.Argument(_selectedMstUnitId);
            var viewController = ViewFactory.Create<UnitSpecialAttackPreviewViewController, UnitSpecialAttackPreviewViewController.Argument>(argument);

            // ヘッダーよりも上に表示するため、HomeViewControllerを使って表示する
            HomeViewController.Show(viewController);
        }

        void UpdateUnitInfo(MasterDataId mstUnitId)
        {
            _selectedMstUnitId = mstUnitId;
            var model = GetEncyclopediaUnitDetailUseCase.GetUnitDetail(mstUnitId);
            var viewModel = Translate(model);
            ViewController.SetupInfo(viewModel);

            ReceiveFirstCollectionReward(mstUnitId);
        }

        void ReceiveFirstCollectionReward(MasterDataId mstUnitId)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                var results = await ReceiveEncyclopediaFirstCollectionRewardUseCase.ReceiveReward(
                    cancellationToken,
                    mstUnitId,
                    EncyclopediaType.Unit
                );

                if (results.Count <= 0) return;

                var rewards = results
                    .Select(r =>
                        CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();

                CommonReceiveWireFrame.Show(rewards);
                HomeHeaderDelegate.UpdateStatus();
            });
        }

        EncyclopediaUnitDetailViewModel Translate(EncyclopediaUnitDetailModel model)
        {
            return new EncyclopediaUnitDetailViewModel(
                model.RoleType,
                model.Rarity,
                model.Name,
                model.UnitAssetKey,
                model.SeriesLogoImagePath,
                model.Description,
                model.SpecialAttackName);
        }
    }
}

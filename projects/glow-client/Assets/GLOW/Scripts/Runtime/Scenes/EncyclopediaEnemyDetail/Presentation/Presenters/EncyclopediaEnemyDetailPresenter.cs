using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Scenes.EncyclopediaEnemyDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaEnemyDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.EncyclopediaEnemyDetail.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-3_作品別キャラ表示
    /// 　　91-3-2_ファントムキャラ表示
    /// </summary>
    public class EncyclopediaEnemyDetailPresenter : IEncyclopediaEnemyDetailViewDelegate
    {
        [Inject] EncyclopediaEnemyDetailViewController ViewController { get; }
        [Inject] EncyclopediaEnemyDetailViewController.Argument Argument { get; }
        [Inject] GetEncyclopediaEnemyDetailUseCase GetEncyclopediaEnemyDetailUseCase { get; }
        [Inject] ReceiveEncyclopediaFirstCollectionRewardUseCase ReceiveEncyclopediaFirstCollectionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        MasterDataId _selectedMstEnemyId;

        void IEncyclopediaEnemyDetailViewDelegate.OnViewDidLoad()
        {
            UpdateUnitInfo(Argument.SelectedMstEnemyCharacterId);
        }

        void IEncyclopediaEnemyDetailViewDelegate.OnSwitchUnit(MasterDataId mstEnemyCharacterId)
        {
            UpdateUnitInfo(mstEnemyCharacterId);
        }

        void IEncyclopediaEnemyDetailViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }

        void UpdateUnitInfo(MasterDataId mstEnemyCharacterId)
        {
            _selectedMstEnemyId = mstEnemyCharacterId;
            var model = GetEncyclopediaEnemyDetailUseCase.GetEnemyDetail(mstEnemyCharacterId);
            var viewModel = Translate(model);
            ViewController.SetupInfo(viewModel);

            ReceiveFirstCollectionReward(mstEnemyCharacterId);
        }

        void ReceiveFirstCollectionReward(MasterDataId mstEnemyCharacterId)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                var results = await ReceiveEncyclopediaFirstCollectionRewardUseCase.ReceiveReward(
                    cancellationToken,
                    mstEnemyCharacterId,
                    EncyclopediaType.EnemyDiscovery
                );

                if (results.Count <= 0) return;

                var rewards =
                    results
                        .Select(r =>
                            CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                        .ToList();
                CommonReceiveWireFrame.Show(rewards);
                HomeHeaderDelegate.UpdateStatus();
            });
        }

        EncyclopediaEnemyDetailViewModel Translate(EncyclopediaEnemyDetailModel model)
        {
            return new EncyclopediaEnemyDetailViewModel(
                model.Name,
                model.SeriesLogoImagePath,
                model.Description);
        }
    }
}

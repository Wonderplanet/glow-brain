using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Scenes.GachaAnim.Domain.UseCases;
using GLOW.Scenes.GachaAnim.Presentation.Translator;
using GLOW.Scenes.GachaAnim.Presentation.ViewModels;
using GLOW.Scenes.GachaAnim.Presentation.Views;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaAnim.Presentation.Presenters
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-3_ガシャ演出
    /// </summary>
    public class GachaAnimPresenter : IGachaAnimViewDelegate
    {
        [Inject] GachaAnimUseCase GachaAnimUseCase { get; }
        [Inject] GachaAnimViewController ViewController { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }

        public void OnViewDidLoad()
        {
            var useCaseModel = GachaAnimUseCase.GetGashaAnimUseCaseModel();
            var iconCellInfos = new List<GachaAnimIconInfo>();
            var resultViewModels = new List<GachaAnimResultViewModel>();

            foreach (var model in useCaseModel.GachaAnimResultModels)
            {
                // スタート演出のサムネイルViewModel作成
                iconCellInfos.Add( GachaAnimIconInfo.CreateGachaAnimIconInfo(model.UnitModel.Rarity, model.ResourceType));

                // 獲得演出時のViewModel作成
                var resultViewModel =
                    GachaAnimTranslator.ToTranslateGachaAnimResultViewModel(model, PlayerResourceModelFactory);

                resultViewModels.Add(resultViewModel);
            }

            var startViewModel = GachaAnimTranslator.ToTranslateGachaAnimStartViewModel(useCaseModel.GachaAnimStartRarity, useCaseModel.GachaAnimEndRarity, iconCellInfos);
            var viewModel = GachaAnimTranslator.ToTranslateGachaAnimViewModel(startViewModel, resultViewModels);

            ViewController.Setup(viewModel);
            ViewController.PlayGashaAnimation();

            // BGMを再生
            BackgroundMusicPlayable.Stop();
            BackgroundMusicPlayable.Play(BGMAssetKeyDefinitions.BGM_gacha_animation);
        }
    }
}

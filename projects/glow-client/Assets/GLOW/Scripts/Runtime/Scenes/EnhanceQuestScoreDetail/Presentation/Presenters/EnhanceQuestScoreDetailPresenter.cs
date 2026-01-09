using GLOW.Scenes.EnhanceQuestScoreDetail.Domain.UseCases;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Translators;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Presenters
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-3-1_スコア獲得条件ダイアログ
    /// </summary>
    public class EnhanceQuestScoreDetailPresenter : IEnhanceQuestScoreDetailViewDelegate
    {
        [Inject] EnhanceQuestScoreDetailViewController ViewController { get; }
        [Inject] EnhanceQuestScoreDetailUseCase UseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }

        void IEnhanceQuestScoreDetailViewDelegate.OnViewDidLoad()
        {
            var model = UseCase.GetScoreDetail();
            var viewModel = EnhanceQuestScoreDetailViewModelTranslator.Translate(model);
            ViewController.Setup(viewModel);
        }

        void IEnhanceQuestScoreDetailViewDelegate.OnBackButtonTapped()
        {
            HomeViewNavigation.TryPop();
        }
    }
}


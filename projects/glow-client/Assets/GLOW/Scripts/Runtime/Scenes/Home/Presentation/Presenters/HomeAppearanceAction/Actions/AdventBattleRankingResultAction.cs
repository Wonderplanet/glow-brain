using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Domain.Models;
using GLOW.Scenes.AdventBattleRankingResult.Domain.UseCases;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Translators;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.ViewModels;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// 降臨バトルランキング結果
    /// </summary>
    public class AdventBattleRankingResultAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<AdventBattleRankingResultAction> { }

        [Inject] AdventBattleRankingResultUseCase AdventBattleRankingResultUseCase { get; }
        [Inject] HomeMainViewController HomeMainViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion, CancellationToken cancellationToken)
        {
            var rankingResultModel = await AdventBattleRankingResultUseCase.GetAdventBattleRankingResult(cancellationToken);

            if (rankingResultModel.IsEmpty() || rankingResultModel.Rank.IsZero())
            {
                // ランキング結果がない場合は何もしない
                return;
            }

            var viewModel = AdventBattleRankingResultViewModelTranslator.ToViewModel(rankingResultModel);

            // 閉じて完了としたいのでUniTaskCompletionSourceを使う
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());

            switch (viewModel.AdventBattleType)
            {
                case AdventBattleType.ScoreChallenge:
                    ShowScoreChallengeRankingResult(viewModel, completionSource);
                    break;

                case AdventBattleType.Raid:
                    ShowRaidBattleRankingResult(viewModel, completionSource);
                    break;
            }

            // 閉じるまで完了を待つ
            await completionSource.Task;
        }
        
        void ShowScoreChallengeRankingResult(
            AdventBattleRankingResultViewModel viewModel, 
            UniTaskCompletionSource completionSource)
        {
            // スコアチャレンジのランキング結果は、閉じると完了とする
            var rankingResultViewController = ViewFactory.Create<AdventBattleRankingResultViewController,
                AdventBattleRankingResultViewController.Argument>
            (
                new AdventBattleRankingResultViewController.Argument(
                    viewModel,
                    false,
                    () => { completionSource.TrySetResult(); }));
            HomeMainViewController.PresentModally(rankingResultViewController);
        }
        
        void ShowRaidBattleRankingResult(
            AdventBattleRankingResultViewModel viewModel,
            UniTaskCompletionSource completionSource)
        {
            // 協力バトルの場合ランキング結果を閉じると協力バトル結果画面を表示
            // 協力バトル結果画面を閉じると完了とする
            var rankingResultViewController = ViewFactory.Create<AdventBattleRankingResultViewController,
                AdventBattleRankingResultViewController.Argument>
            (
                new AdventBattleRankingResultViewController.Argument(
                    viewModel,
                    true,
                    () => { ShowRaidBattleResult(viewModel, completionSource); }));
            HomeMainViewController.PresentModally(rankingResultViewController);
        }

        void ShowRaidBattleResult(
            AdventBattleRankingResultViewModel viewModel,
            UniTaskCompletionSource completionSource)
        {
            var raidRankingResultViewController = ViewFactory.Create<AdventBattleRaidRankingResultViewController,
                AdventBattleRaidRankingResultViewController.Argument>
            (
                new AdventBattleRaidRankingResultViewController.Argument(
                    viewModel,
                    () => { completionSource.TrySetResult(); }));
            HomeMainViewController.PresentModally(raidRankingResultViewController);
        }
    }
}

using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.AdventBattleRaidRankingResult.Presentation.Views;
using GLOW.Scenes.AdventBattleRankingResult.Domain.Models;
using GLOW.Scenes.AdventBattleRankingResult.Domain.UseCases;
using GLOW.Scenes.AdventBattleRankingResult.Presentation.Translators;
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
                    var rankingResultViewController = ViewFactory.Create<AdventBattleRankingResultViewController,
                        AdventBattleRankingResultViewController.Argument>
                    (
                        new AdventBattleRankingResultViewController.Argument(
                            viewModel,
                            () => { completionSource.TrySetResult(); }));
                    HomeMainViewController.PresentModally(rankingResultViewController);
                    break;

                case AdventBattleType.Raid:
                    var raidRankingResultViewController = ViewFactory.Create<AdventBattleRaidRankingResultViewController,
                        AdventBattleRaidRankingResultViewController.Argument>
                    (
                        new AdventBattleRaidRankingResultViewController.Argument(
                            viewModel,
                            () => { completionSource.TrySetResult(); }));
                    HomeMainViewController.PresentModally(raidRankingResultViewController);
                    break;
            }

            // 閉じるまで完了を待つ
            await completionSource.Task;
        }
    }
}

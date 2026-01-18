using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.Views.FinishResult;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Presenters
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-6_リザルト画面
    /// </summary>
    public class FinishResultPresenter : IFinishResultViewDelegate
    {
        const string TapForCloseText = "タップで閉じる";

        [Inject] FinishResultViewController ViewController { get; }
        [Inject] FinishResultViewController.Argument Argument { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }

        bool _isSlideInAnimationCompleted;
        bool _isScoreAnimationCompleted;
        bool _isResultAnimationCompleted;
        readonly CancellationTokenSource _resultSlideInAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _resultRewardAnimationCancellationTokenSource = new ();
        readonly CancellationTokenSource _playScoreAnimationCancellationTokenSource = new ();

        public void OnViewDidLoad()
        {
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                BackgroundMusicPlayable.PlayWithCrossFade(cancellationToken, BGMAssetKeyDefinitions.BGM_victory_result, 0.2f);
                
                var resultScoreModel = Argument.ViewModel.ResultScoreModel;
                ViewController.SetScoreText(InGameScore.Zero);
                ViewController.SetHighScoreText(resultScoreModel.HighScore, resultScoreModel.NewRecordFlag);
                ViewController.SetUpEventCampaignBalloon(Argument.ViewModel.RemainingEventCampaignTimeSpan);
                ViewController.SetupRetryButton(Argument.ViewModel.RetryAvailableFlag);

                await PlaySlideInAnimation(cancellationToken);
                await PlayScoreAnimation(
                    resultScoreModel.CurrentScore,
                    resultScoreModel.NewRecordFlag,
                    cancellationToken);
                await PlayResultRewardAnimation(cancellationToken);
                await UniTask.Delay(TimeSpan.FromSeconds(0.2f), cancellationToken:cancellationToken);
                ViewController.SetRewardMultiplierText(resultScoreModel.TotalBonusPercentage);

                ViewController.HideSkipScreenButton();
                ViewController.ShowTapLabel(TapForCloseText);
                _isSlideInAnimationCompleted = true;
                _isScoreAnimationCompleted = true;
                _isResultAnimationCompleted = true;
                
                // アニメーション完了後に再挑戦ボタンを有効化
                ViewController.SetActiveRetryButton(Argument.ViewModel.RetryAvailableFlag);
            });
        }

        public void OnUnloadView()
        {
            _resultSlideInAnimationCancellationTokenSource?.Dispose();
            _playScoreAnimationCancellationTokenSource?.Dispose();
            _resultRewardAnimationCancellationTokenSource?.Dispose();
        }

        async UniTask PlaySlideInAnimation(CancellationToken cancellationToken)
        {
            var resultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _resultSlideInAnimationCancellationTokenSource.Token).Token;

            var resultSlideInAnimationCanceled = await ViewController.PlaySlideInAnimation(resultAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            // 演出スキップしたときは即時スライドイン後の状態に
            if (resultSlideInAnimationCanceled)
            {
                ViewController.SkipSlideInAnimation();
            }
            _isSlideInAnimationCompleted = true;
        }

        async UniTask PlayScoreAnimation(
            InGameScore currentScore,
            NewRecordFlag newRecordFlag,
            CancellationToken cancellationToken)
        {
            var scoreAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _playScoreAnimationCancellationTokenSource.Token).Token;

            // 0 ~ 獲得スコアまでの上昇アニメーション処理
            var increaseScoreAnimationCanceled = await ViewController.IncreaseScoreAnimation(
                currentScore,
                scoreAnimationCancellationToken).SuppressCancellationThrow();
            cancellationToken.ThrowIfCancellationRequested();

            // 演出スキップしたときは即時表示
            if (increaseScoreAnimationCanceled)
            {
                SkipScore(currentScore, newRecordFlag);
                _isScoreAnimationCompleted = true;
                return;
            }

            // 獲得スコアまで表示された後にスコアとハイスコアのアニメーション
            var uniTasks = new List<UniTask>
            {
                ViewController.PlayScoreAnimation(scoreAnimationCancellationToken).SuppressCancellationThrow()
            };
            if (newRecordFlag)
            {
                uniTasks.Add(ViewController.PlayNewRecordAnimation(scoreAnimationCancellationToken).SuppressCancellationThrow());
            }

            var resultScoreAnimationCanceled = await UniTask.WhenAll(uniTasks).SuppressCancellationThrow();
            cancellationToken.ThrowIfCancellationRequested();

            // 演出スキップしたときは即時表示
            if (resultScoreAnimationCanceled)
            {
                SkipScore(currentScore, newRecordFlag);
            }
            _isScoreAnimationCompleted = true;
        }

        async UniTask PlayResultRewardAnimation(CancellationToken cancellationToken)
        {
            var rewardResultAnimationCancellationToken = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken, _resultRewardAnimationCancellationTokenSource.Token).Token;

            var rewardResultAnimationCanceled = await ViewController.PlayAcquiredItemsAnimation(
                    Argument.ViewModel.AcquiredPlayerResources,
                    rewardResultAnimationCancellationToken)
                .SuppressCancellationThrow();

            cancellationToken.ThrowIfCancellationRequested();

            // 演出スキップしたときは即座に結果を表示する
            if (rewardResultAnimationCanceled)
            {
                ViewController.SkipExpandRewardList();
                ViewController.ShowAcquiredItems(Argument.ViewModel.AcquiredPlayerResources);
            }
            _isResultAnimationCompleted = true;
        }

        public void OnSkipSelected()
        {
            // スライドインアニメーションのキャンセルして即時スライドイン後の表示に
            if (!_isResultAnimationCompleted && !_isSlideInAnimationCompleted)
            {
                _resultSlideInAnimationCancellationTokenSource.Cancel();
                return;
            }

            // スコア周りのアニメーションを即時表示
            if (!_isResultAnimationCompleted && !_isScoreAnimationCompleted)
            {
                _playScoreAnimationCancellationTokenSource.Cancel();
                return;
            }

            // 報酬の演出までをキャンセルして即座に結果を表示する
            if (!_isResultAnimationCompleted)
            {
                _resultRewardAnimationCancellationTokenSource.Cancel();
            }
        }

        public void OnCloseSelected()
        {
            ViewController.Dismiss(animated:false, completion:Argument.OnViewClosed);
        }

        public void OnBackButton()
        {
            OnSkipSelected();
            if (!_isResultAnimationCompleted) return;
            OnCloseSelected();
        }
        
        void IFinishResultViewDelegate.OnRetrySelected()
        {
            Argument.OnRetrySelected?.Invoke();
        }

        void IFinishResultViewDelegate.OnIconSelected(PlayerResourceIconViewModel viewModel)
        {
            if (!_isResultAnimationCompleted) return;
            ItemDetailWireFrame.ShowNoTransitionLayoutItemDetailView(viewModel, ViewController);
        }

        void SkipScore(InGameScore currentScore, NewRecordFlag newRecordFlag)
        {
            ViewController.SetScoreText(currentScore);
            ViewController.SkipScoreAnimation();
            if (newRecordFlag)
            {
                ViewController.SkipNewRecordAnimation();
            }
        }
    }
}

using System;
using System.Collections.Generic;
using System.Threading;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using GLOW.Scenes.PvpPreviousSeasonResult.Presentation.ViewModels;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Presentation.ViewModels;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Scenes.BattleResult.Presentation.Components;
using WonderPlanet.UniTaskSupporter;

namespace GLOW.Scenes.PvpPreviousSeasonResult.Presentation.Views
{
    public class PvpPreviousSeasonResultView : UIView
    {
        static readonly int Rank = Animator.StringToHash("Rank");
        static readonly int TriggerIn = Animator.StringToHash("in");

        [SerializeField] Animator _animator;
        [SerializeField] UIText _rankingText;
        [SerializeField] UIText _totalPointText;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] UIText _rankingRankText;
        [SerializeField] PlayerResourceIconList _playerResourceIconList;
        [SerializeField] VictoryResultTapLabelComponent _tabLabel;
        [SerializeField] UIObject _closeButton;

        public void SetUp(PvpPreviousSeasonResultViewModel viewModel)
        {
            SetRankText(viewModel.Ranking);
            SetTotalPointText(viewModel.Point);
            SetRankingRankText(viewModel.PvpRankClassType, viewModel.RankClassLevel);
            SetRankingRankIcon(viewModel.PvpRankClassType, viewModel.RankClassLevel);
            _closeButton.IsVisible = false;

            DoAsync.Invoke(this.GetCancellationTokenOnDestroy(), async cancellationToken =>
            {
                await PlayRankAnimation(viewModel.Ranking, cancellationToken);
                await ShowPlayerResourceIconList(viewModel.PvpRewards, cancellationToken);
                _closeButton.IsVisible = true;
                _tabLabel.Show("タップして閉じる");
            });
        }

        void SetRankText(PvpRankingRank rank)
        {
            _rankingText.SetText(rank.ToDisplayString());
        }

        void SetTotalPointText(PvpPoint point)
        {
            _totalPointText.SetText(point.ToStringSeparate());
        }

        void SetRankingRankIcon(PvpRankClassType rank, PvpRankLevel rankLevel)
        {
            _rankingRankIcon.SetupRankType(rank);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel);
        }

        void SetRankingRankText(PvpRankClassType rankClass, PvpRankLevel rankLevel)
        {
            _rankingRankText.SetText(rankClass.ToDisplayStringWithRankLevel(rankLevel));
        }

        async UniTask ShowPlayerResourceIconList(
            IReadOnlyList<PlayerResourceIconViewModel> rewards,
            CancellationToken cancellationToken)
        {
            var isComplete = false;
            _playerResourceIconList.SetupAndReload(
                rewards,
                onComplete:() => isComplete = true);
            await UniTask.WaitUntil(() => isComplete, cancellationToken: cancellationToken);
        }

        public async UniTask PlayRankAnimation(PvpRankingRank ranking, CancellationToken cancellationToken)
        {
            int rankAnimValue = (int)ranking.Value switch
            {
                0 => 4, // ランク未参加
                1 => 1, // １位
                2 => 2, // ２位
                3 => 3, // ３位
                _ => 0  // 通常ランク
            };

            _animator.SetInteger(Rank, rankAnimValue);
            _animator.SetTrigger(TriggerIn);

            // アニメーション終了まで待機
            await WaitForAnimationEnd(_animator, 1, cancellationToken);
        }

        async UniTask WaitForAnimationEnd(Animator animator, int layerIndex, CancellationToken cancellationToken)
        {
            // AnimatorがendStateNameのステートに到達し、かつ遷移中でないことを確認
            while (true)
            {
                if (cancellationToken.IsCancellationRequested)
                {
                    throw new OperationCanceledException(cancellationToken);
                }

                var stateInfo = animator.GetCurrentAnimatorStateInfo(layerIndex);
                // ステート名がendStateNameで、遷移中でなければ終了
                if (stateInfo.IsName("RankingResultPanel@Loop") && !animator.IsInTransition(layerIndex))
                {
                    break;
                }

                await UniTask.Yield(cancellationToken);
            }
        }
    }
}

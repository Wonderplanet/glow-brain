using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.BattleResult.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRankingResult.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-3_ランキング結果表示ダイアログ
    /// </summary>
    public class AdventBattleRankingResultView : UIView
    {
        static readonly int Rank = Animator.StringToHash("Rank");
        static readonly int TriggerIn = Animator.StringToHash("in");

        [SerializeField] Animator _animator;
        [SerializeField] UIText _adventBattleTitleNameText;
        [SerializeField] UIText _rankTierText;
        [SerializeField] UIText _scoreText;
        [SerializeField] RankingRankIcon _rankingRankIcon;
        [SerializeField] UIText _rankText;
        [SerializeField] PlayerResourceIconList _iconList;
        [SerializeField] VictoryResultTapLabelComponent _tabLabel;
        [SerializeField] UIObject _closeButton;

        public void SetUpAdventBattleTitle(AdventBattleName adventBattleName)
        {
            _adventBattleTitleNameText.SetText(adventBattleName.ToString());
        }

        public void SetUpRankIcon(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            _rankingRankIcon.SetupRankType(rankType);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
        }

        public void SetUpRankTierText(AdventBattleRankingRank rank)
        {
            _rankTierText.SetText(rank.ToDisplayString());
        }

        public void SetUpRankText(RankType rankType, AdventBattleScoreRankLevel rankLevel)
        {
            _rankText.SetText(rankType.ToDisplayStringWithRankLevel(rankLevel));
        }

        public void SetUpScoreText(AdventBattleScore score)
        {
            _scoreText.SetText(score.ToDisplayString());
        }

        public async UniTask PlayRankAnimation(AdventBattleRankingRank ranking, CancellationToken cancellationToken)
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

        public async UniTask PlayAcquiredItemsAnimation(
            IReadOnlyList<PlayerResourceIconViewModel> rewardViewModels,
            CancellationToken cancellationToken)
        {
            if (rewardViewModels.Count <= 0)
            {
                return;
            }

            var isCellAnimationCompleted = false;
            _iconList.SetupAndReload(rewardViewModels, true, 1, onComplete:() => isCellAnimationCompleted = true);

            await UniTask.WaitUntil(() => isCellAnimationCompleted, cancellationToken: cancellationToken);
        }

        public void SetUpCloseTextAndButton()
        {
            _tabLabel.Show("タップで閉じる");
            _closeButton.IsVisible = true;
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

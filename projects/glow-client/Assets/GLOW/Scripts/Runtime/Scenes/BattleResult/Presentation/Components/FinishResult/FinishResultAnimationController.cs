using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components.FinishResult
{
    /// <summary>
    /// 強化クエストなどでのFinishリザルト画面のアニメーション操作を取り扱う。
    /// アニメーション自体がVictoryの流用であり、現段階ではVictoryResultAnimationControllerと同一
    /// </summary>
    public class FinishResultAnimationController : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] Animator _resultPanelAnimator;
        [SerializeField] Animator _scoreAnimator;
        [SerializeField] Animator _newRecordAnimator;
        [SerializeField] string _slideInStateName;
        [SerializeField] string _rewardListExpansionStateName;
        [SerializeField] string _scoreStateName;
        [SerializeField] string _newRecordStateName;

        public void SkipSlideIn()
        {
            _timelineAnimation.Skip();

            _resultPanelAnimator.gameObject.SetActive(true);
            _resultPanelAnimator.Play(_slideInStateName, 0, 1);
        }

        public void SkipScore()
        {
            _scoreAnimator.Play(_scoreStateName, 0, 1);
        }

        public void SkipNewRecord()
        {
            _newRecordAnimator.Play(_scoreStateName, 0, 1);
        }

        public void SkipExpandRewardList()
        {
            _resultPanelAnimator.Play(_rewardListExpansionStateName, 0, 1);
        }

        public async UniTask SlideIn(CancellationToken cancellationToken)
        {
            _timelineAnimation.Play();

            // Timeline全体が終わるのを待ってると長いので、ResultPanelのスライドインアニメーションが終わるタイミングで抜ける
            await WaitResultPanelAnimationComplete(cancellationToken);
        }

        public async UniTask ShowScore(CancellationToken cancellationToken)
        {
            _scoreAnimator.Play(_scoreStateName);
            await WaitScoreAnimationComplete(cancellationToken);
        }

        public async UniTask NewRecord(CancellationToken cancellationToken)
        {
            _newRecordAnimator.Play(_newRecordStateName);
            await WaitHighScoreAnimationComplete(cancellationToken);
        }

        public async UniTask ExpandRewardList(CancellationToken cancellationToken)
        {
            _resultPanelAnimator.Play(_rewardListExpansionStateName);

            await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);

            await WaitResultPanelAnimationComplete(cancellationToken);
        }

        async UniTask WaitScoreAnimationComplete(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(
                () => _scoreAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }

        async UniTask WaitHighScoreAnimationComplete(CancellationToken cancellationToken)
        {
            // 最初のStateから途中で次のStateに遷移するため遷移前かを確認（最初のStateの場合のみ待つ）
            var currentClip = _newRecordAnimator.GetCurrentAnimatorClipInfo(0);
            if (currentClip.Length > 0 && currentClip[0].clip.name == _newRecordStateName)
            {
                await UniTask.Delay(TimeSpan.FromSeconds(currentClip[0].clip.length), cancellationToken:cancellationToken);
            }
        }

        async UniTask WaitResultPanelAnimationComplete(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(
                () => _resultPanelAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }
    }
}

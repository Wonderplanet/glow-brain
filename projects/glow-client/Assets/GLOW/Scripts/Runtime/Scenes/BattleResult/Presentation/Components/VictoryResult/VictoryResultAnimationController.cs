using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components
{
    public class VictoryResultAnimationController : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] Animator _resultPanelAnimator;
        [SerializeField] string _slideInStateName;
        [SerializeField] string _rewardListExpansionStateName;

        public void SkipSlideIn()
        {
            _timelineAnimation.Skip();

            _resultPanelAnimator.gameObject.SetActive(true);
            _resultPanelAnimator.Play(_slideInStateName, 0, 1);
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

        public async UniTask ExpandRewardList(CancellationToken cancellationToken)
        {
            _resultPanelAnimator.Play(_rewardListExpansionStateName);

            await UniTask.Yield(PlayerLoopTiming.Update, cancellationToken);

            await WaitResultPanelAnimationComplete(cancellationToken);
        }

        async UniTask WaitResultPanelAnimationComplete(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(
                () => _resultPanelAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);
        }
    }
}

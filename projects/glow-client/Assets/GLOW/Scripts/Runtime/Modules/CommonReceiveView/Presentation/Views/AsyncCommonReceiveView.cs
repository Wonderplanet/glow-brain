using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Views.Interaction;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.CommonReceiveView.Presentation.Views
{
    public class AsyncCommonReceiveView : CommonReceiveView
    {
        [SerializeField] CommonLoadingView _loadingView;
        [SerializeField] Animator _rewardLabelAnimator;
        [SerializeField] VerticalLayoutGroup _layoutGroup;

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            await WaitRewardLabelAnimationComplete(cancellationToken);
        }

        async UniTask WaitRewardLabelAnimationComplete(CancellationToken cancellationToken)
        {
            _rewardLabelAnimator.Play("Appearing");
            await UniTask.WaitUntil(() =>  _rewardLabelAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 0.6, cancellationToken: cancellationToken);
        }

        public void UpdateLayout()
        {
            _layoutGroup.CalculateLayoutInputVertical();
            _layoutGroup.SetLayoutVertical();
        }

        public void StartLoading()
        {
            _loadingView.Hidden = false;

            _loadingView.Animate("appear");
            _loadingView.StartAnimation();
        }

        public void StopLoading()
        {
            _loadingView.Hidden = true;

            _loadingView.StopAnimation();
        }
    }
}

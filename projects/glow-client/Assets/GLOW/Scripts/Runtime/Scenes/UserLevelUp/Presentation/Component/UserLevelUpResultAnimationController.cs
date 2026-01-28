using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.UserLevelUp.Presentation.Component
{
    public class UserLevelUpResultAnimationController : UIBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] Animator _logoAnimator;
        [SerializeField] Animator _rippleAnimator;
        [SerializeField] Animator _flashAnimator;
        [SerializeField] string _logoDefAnimationName;
        [SerializeField] string _rippleDefAnimationName;
        [SerializeField] string _flashDefAnimationName;

        public void Skip()
        {
            _timelineAnimation.Skip();

            _logoAnimator.Play(_logoDefAnimationName, 0, 1);
            _rippleAnimator.Play(_rippleDefAnimationName, 0, 1);
            _flashAnimator.Play(_flashDefAnimationName, 0, 1);
        }

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            _timelineAnimation.Play();

            // ロゴが出てき始めたあたりから次の演出再生に動かす
            await StartLogoAnimationPlaying(cancellationToken);
        }

        async UniTask StartLogoAnimationPlaying(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(() =>  _logoAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 0.05f, cancellationToken: cancellationToken);
        }
    }
}

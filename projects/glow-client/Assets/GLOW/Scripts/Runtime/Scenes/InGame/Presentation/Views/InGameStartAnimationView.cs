using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public class InGameStartAnimationView : UIView
    {
        [SerializeField] Animator _animator;

        CancellationToken CancellationToken => this.GetCancellationTokenOnDestroy();

        public Action OnCompleted { get; set; }

        protected override void Awake()
        {
            base.Awake();
            WaitComplete(CancellationToken).Forget();
        }

        async UniTask WaitComplete(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);

            OnCompleted?.Invoke();
        }
    }
}

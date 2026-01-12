using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components
{
    public class VictoryResultLevelUpFx : UIObject
    {
        [SerializeField] Animator _animator;

        CancellationToken CancellationToken => this.GetCancellationTokenOnDestroy();

        protected override void Awake()
        {
            base.Awake();
            WaitCompleteAndDestroy(CancellationToken).Forget();
        }

        async UniTask WaitCompleteAndDestroy(CancellationToken cancellationToken)
        {
            await UniTask.WaitUntil(
                () => _animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1,
                cancellationToken:cancellationToken);

            Destroy(gameObject);
        }
    }
}

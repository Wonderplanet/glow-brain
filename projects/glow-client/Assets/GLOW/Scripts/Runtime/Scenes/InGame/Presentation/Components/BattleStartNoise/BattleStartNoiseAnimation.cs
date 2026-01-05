using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class BattleStartNoiseAnimation : MonoBehaviour
    {
        static readonly int TriggerId_in = Animator.StringToHash("in");
        static readonly int TriggerId_out = Animator.StringToHash("out");

        [SerializeField] Animator _animator;

        public async UniTask Play(CancellationToken cancellationToken)
        {
            _animator.SetTrigger(TriggerId_in);
            await UniTask.Delay(2500, cancellationToken:cancellationToken);
            _animator.SetTrigger(TriggerId_out);
            await UniTask.Delay(600, cancellationToken:cancellationToken);
        }
    }
}
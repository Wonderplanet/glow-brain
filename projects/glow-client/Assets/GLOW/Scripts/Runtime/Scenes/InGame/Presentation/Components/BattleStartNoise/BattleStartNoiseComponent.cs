using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class BattleStartNoiseComponent : MonoBehaviour
    {
        [SerializeField] BattleStartNoiseAnimation _prefab;
        
        public async UniTask Play(CancellationToken cancellationToken)
        {
            var battleStartNoiseAnimation = Instantiate(_prefab, transform);

            try
            {
                await battleStartNoiseAnimation.Play(cancellationToken);
            }
            finally
            {
                Destroy(battleStartNoiseAnimation.gameObject);
            }
        }
    }
}
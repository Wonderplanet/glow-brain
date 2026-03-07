using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Components
{
    public class HomeHeaderLevelUpEffect : UIObject
    {
        [SerializeField] AnimationPlayer _animationPlayer;
        
        public async UniTask PlayLevelUpEffectAsync(CancellationToken cancellationToken)
        {
            Hidden = false;
            await _animationPlayer.PlayAsync(cancellationToken);
            Hidden = true;
        }
    }
}
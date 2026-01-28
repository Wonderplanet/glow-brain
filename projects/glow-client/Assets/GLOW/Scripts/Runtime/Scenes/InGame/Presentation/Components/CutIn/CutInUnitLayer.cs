using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    [RequireComponent(typeof(TimelineAnimation))]
    public class CutInUnitLayer : MonoBehaviour
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] Transform _unitRoot;

        public Transform UnitRoot => _unitRoot;

        public async UniTask PlayAsync(CancellationToken cancellationToken)
        {
            if (_timelineAnimation != null)
            {
                await _timelineAnimation.PlayAsync(cancellationToken);
            }
        }

        public void Pause(bool pause)
        {
            if (_timelineAnimation != null)
            {
                _timelineAnimation.Pause(pause);
            }
        }
    }
}

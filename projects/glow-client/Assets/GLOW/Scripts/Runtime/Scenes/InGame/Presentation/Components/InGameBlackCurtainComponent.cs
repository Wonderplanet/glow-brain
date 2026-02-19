using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameBlackCurtainComponent : UIObject
    {
        [SerializeField] UIImage _curtainImage;
        [SerializeField] float _defaultAlpha = 1f;

        public void Show()
        {
            Hidden = false;
            _curtainImage.Alpha = _defaultAlpha;
        }
        
        public async UniTask Show(CancellationToken cancellationToken)
        {
            await Show(_defaultAlpha, cancellationToken);
        }

        public async UniTask Show(float alpha, CancellationToken cancellationToken)
        {
            Hidden = false;

            _curtainImage.Alpha = 0f;
            await _curtainImage.Image
                .DOFade(alpha, 0.2f)
                .ToUniTask(cancellationToken: cancellationToken);
        }
        
        public void Hide()
        {
            Hidden = true;
        }
        
        public async UniTask Hide(CancellationToken cancellationToken)
        {
            await _curtainImage.Image
                .DOFade(0f, 0.2f)
                .OnComplete(() => Hidden = true)
                .ToUniTask(TweenCancelBehaviour.Complete, cancellationToken: cancellationToken);
        }
    }
}

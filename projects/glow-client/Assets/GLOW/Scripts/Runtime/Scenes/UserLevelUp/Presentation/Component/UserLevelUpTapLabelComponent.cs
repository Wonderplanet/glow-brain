using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UserLevelUp.Presentation.Component
{
    public class UserLevelUpTapLabelComponent : UIObject
    {
        [SerializeField] CanvasGroup _tapLabelCanvasGroup;

        public async UniTask PlayFadeIn(CancellationToken cancellationToken)
        {
            _tapLabelCanvasGroup.alpha = 0.0f;
            Hidden = false;

            await _tapLabelCanvasGroup.DOFade(1.0f, 0.2f).SetEase(Ease.Linear).WithCancellation(cancellationToken);
        }

        public void ShowCloseLabel()
        {
            _tapLabelCanvasGroup.alpha = 1.0f;
            Hidden = false;
        }
    }
}

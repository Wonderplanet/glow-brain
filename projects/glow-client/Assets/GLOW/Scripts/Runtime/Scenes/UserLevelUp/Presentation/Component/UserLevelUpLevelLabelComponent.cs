using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.UserLevelUp.Presentation.Component
{
    public class UserLevelUpLevelLabelComponent : UIObject
    {
        [SerializeField] CanvasGroup _levelLabelCanvasGroup;

        [SerializeField] UIText _levelNumberText;

        [SerializeField] UIText _levelMaxText;

        public void SetUserLevel(UserLevel userLevel, bool isLevelMax)
        {
            _levelLabelCanvasGroup.alpha = 0.0f;
            _levelMaxText.Hidden = !isLevelMax;
            _levelNumberText.SetText(userLevel.ToStringAmount());
        }

        public async UniTask PlayFadeIn(CancellationToken cancellationToken)
        {
            _levelLabelCanvasGroup.alpha = 0.0f;

            await _levelLabelCanvasGroup.DOFade(1.0f, 0.2f).SetEase(Ease.Linear).WithCancellation(cancellationToken);
        }

        public void ShowUserLevel()
        {
            _levelLabelCanvasGroup.alpha = 1.0f;
        }
    }
}

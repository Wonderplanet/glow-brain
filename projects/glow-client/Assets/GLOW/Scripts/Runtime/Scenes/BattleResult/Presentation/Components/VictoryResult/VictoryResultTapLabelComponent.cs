using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components
{
    public class VictoryResultTapLabelComponent : UIObject
    {
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] UIText _labelText;

        Tween _tween;

        public void Show(string text)
        {
            Hidden = false;

            _labelText.SetText(text);

            _tween?.Kill();
            _canvasGroup.alpha = 0f;
            _tween = _canvasGroup.DOFade(1f, 0.2f).SetEase(Ease.Linear);
        }

        public void Hide()
        {
            _tween?.Kill();
            Hidden = true;
        }
    }
}

using System;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.InGame.Presentation.Components.InGameUnitDetail
{
    public class InGameUnitDetailTouchLayerComponent : MonoBehaviour
    {
        [SerializeField] Button _touchLayerButton;

        public void SetTapAction(Action onTapped)
        {
            _touchLayerButton.onClick.RemoveAllListeners();
            _touchLayerButton.onClick.AddListener(() => onTapped?.Invoke());
        }
    }
}

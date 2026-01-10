using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.Serialization;
using UnityEngine.UI;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public class OutpostEnhanceArtworkChangeComponent : UIObject
    {
        [SerializeField] OutpostEnhanceGateButtonGrayOutComponent _outpostButtonGrayOutComponent;
        [SerializeField] Button _artworkChangeButton;

        public bool Interactable
        {
            get => _artworkChangeButton.interactable;
            private set => _artworkChangeButton.interactable = value;
        }

        public void SetActiveArtworkChangeButton(bool isActive)
        {
            _artworkChangeButton.gameObject.SetActive(isActive);

            if (!isActive) return;

            if (Interactable)
            {
                _outpostButtonGrayOutComponent.FadeOutGrayOut();
            }
            else
            {
                _outpostButtonGrayOutComponent.FadeInGrayOut();
            }
        }

        public void SetInteractableArtworkChangeButton(bool isInteractable)
        {
            if (isInteractable)
            {
                _outpostButtonGrayOutComponent.FadeOutGrayOut();
            }
            else
            {
                _outpostButtonGrayOutComponent.FadeInGrayOut();
            }

            Interactable = isInteractable;
        }
    }
}

using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;
using WPFramework.Modules.Log;
using WPFramework.Presentation.Modules;

namespace WPFramework.Presentation.Components
{
    [RequireComponent(typeof(Button))]
    public class UIButtonSound : MonoBehaviour, IPointerClickHandler
    {
        [SerializeField] string _soundIdentifier = string.Empty;

        Button _button;

        void Awake()
        {
            _button = GetComponent<Button>();
        }

        void IPointerClickHandler.OnPointerClick(PointerEventData eventData)
        {
            if (!_button.interactable)
            {
                return;
            }

            PlaySound();
        }

        public void PlaySound()
        {
            if (string.IsNullOrEmpty(_soundIdentifier))
            {
                ApplicationLog.LogWarning(nameof(UIButtonSound), $"{nameof(PlaySound)}: Sound identifier is empty.");
                return;
            }

            UISoundEffector.Main.Play(_soundIdentifier);
        }
    }
}

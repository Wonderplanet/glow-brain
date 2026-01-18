using UIKit;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class UIToggleableComponent : UIObject, IToggleable
    {
        [SerializeField] GameObject _toggleOnObject;
        [SerializeField] GameObject _toggleOffObject;

        bool _isToggleOn;

        public bool IsToggleOn
        {
            get => _isToggleOn;

            set
            {
                _isToggleOn = value;
                _toggleOnObject.SetActive(_isToggleOn);
                _toggleOffObject.SetActive(!_isToggleOn);
            }
        }
    }
}

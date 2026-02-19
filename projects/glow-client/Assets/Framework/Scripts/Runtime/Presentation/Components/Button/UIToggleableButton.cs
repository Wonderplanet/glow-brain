using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Presentation.Components
{
    [RequireComponent(typeof(Button))]
    [RequireComponent(typeof(Animator))]
    public class UIToggleableButton : MonoBehaviour, IToggleable
    {
        const string AnimationParameterKey = "ToggleOn";

        Animator _animator;
        bool isToggleOn;
        Button _button;

        public Button Button
        {
            get
            {
                if (!_button)
                {
                    _button = GetComponent<Button>();
                }

                return _button;
            }
        }

        public bool IsToggleOn
        {
            get => isToggleOn;
            set
            {
                isToggleOn = value;

                if (!_animator)
                {
                    _animator = GetComponent<Animator>();
                }

                _animator.SetBool(AnimationParameterKey, isToggleOn);
            }
        }
    }
}

using System;
using UIKit;
using UnityEngine;
using UnityEngine.Events;
using UnityEngine.UI;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugInputViewCell : UICollectionViewCell
    {
        [SerializeField] Text _nameText;
        [SerializeField] Text _descriptionText;
        [SerializeField] Text _placeholderText;
        [SerializeField] InputField _inputField;

        public string NameText { set => _nameText.text = value; }
        public string DescriptionText { set => _descriptionText.text = value; }
        public string PlaceholderText { set => _placeholderText.text = value; }

        public InputField.ContentType InputFieldContentType
        {
            set => _inputField.contentType = value;
        }

        public string InputFieldText
        {
            get => _inputField.text;
            set => _inputField.text = value;
        }

        public UnityAction<string> OnInputFieldValueChanged
        {
            set
            {
                _inputField.onValueChanged.RemoveAllListeners();
                _inputField.onValueChanged.AddListener(value);
            }
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _inputField.onValueChanged.RemoveAllListeners();
        }
    }
}

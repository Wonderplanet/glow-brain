using System;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public class DebugEnvironmentSpecifiedDomainCollectionCell : UICollectionViewCell
    {
        [SerializeField] Text _nameText;
        [SerializeField] InputField _inputField;

        public Action<string, string> EndEditAction { private get; set; }

        public string NameText
        {
            get => _nameText.text;
            set => _nameText.text = value;
        }

        public string InputFieldText
        {
            get => _inputField.text;
            set => _inputField.text = value;
        }

        protected override void Start()
        {
            _inputField.onEndEdit.AddListener(OnEndEdit);
        }

        void OnEndEdit(string text)
        {
            EndEditAction?.Invoke(NameText, text);
        }
    }
}

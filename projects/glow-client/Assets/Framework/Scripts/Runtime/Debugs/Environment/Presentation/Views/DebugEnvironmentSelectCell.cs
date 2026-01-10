using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public sealed class DebugEnvironmentSelectCell : UICollectionViewCell
    {
        [SerializeField] Text _environmentText;
        [SerializeField] Text _connectApiUrlText;
        [SerializeField] Text _descriptionText;

        public string EnvironmentText
        {
            get => _environmentText.text;
            set => _environmentText.text = value;
        }
        public string ConnectApiUrlText
        {
            get => _connectApiUrlText.text;
            set => _connectApiUrlText.text = value;
        }
        public string DescriptionText
        {
            get => _descriptionText.text;
            set => _descriptionText.text = value;
        }
    }
}

using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public class AdminDebugViewCell : UICollectionViewCell
    {
        [SerializeField] Text _nameText;
        [SerializeField] Text _descriptionText;

        public string NameText { set => _nameText.text = value; }
        public string DescriptionText { set => _descriptionText.text = value; }
    }
}

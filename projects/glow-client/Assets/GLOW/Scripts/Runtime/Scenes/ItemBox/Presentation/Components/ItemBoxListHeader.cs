using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Components
{
    public class ItemBoxListHeader : UICollectionViewSectionHeader
    {
        [SerializeField] GameObject _fragmentBoxHeader;
        [SerializeField] GameObject _otherHeader;

        public void SetFragmentBoxHeader()
        {
            _fragmentBoxHeader.SetActive(true);
            _otherHeader.SetActive(false);
        }

        public void SetOtherHeader()
        {
            _fragmentBoxHeader.SetActive(false);
            _otherHeader.SetActive(true);
        }
    }
}

using UIKit;
using UnityEngine;

namespace WPFramework.Presentation.Views
{
    public class NavigationView : UIView
    {
        [SerializeField] NavigationBar _navigationBar = null;

        [SerializeField] RectTransform _content = null;
        [SerializeField] RectTransform _bgContent = null;
        [SerializeField] UIView _bgSourceContainer = null;
        [SerializeField] UIView _bgDestinationContainer = null;
        [SerializeField] UIView _sourceContainer = null;
        [SerializeField] UIView _destinationContainer = null;

        public RectTransform Content => _content;
        public RectTransform BgContent => _bgContent;
        public NavigationBar NavigationBar => _navigationBar;

        public UIView BgSourceContainer => _bgSourceContainer;
        public UIView BgDestinationContainer => _bgDestinationContainer;
        public UIView SourceContainer => _sourceContainer;
        public UIView DestinationContainer => _destinationContainer;
    }
}

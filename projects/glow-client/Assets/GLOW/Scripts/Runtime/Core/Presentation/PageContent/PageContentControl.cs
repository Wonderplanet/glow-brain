using System;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.PageContent
{
    public class PageContentControl : UIComponent
    {
        [SerializeField] Button _beforeButton;
        [SerializeField] Button _nextButton;

        public Action<int> OnClickEvent = (_) => { };

        int _numberOfPages;
        int _currentPage;

        public int CurrentPage
        {
            get => _currentPage;
            set
            {
                _currentPage = (value + _numberOfPages) % _numberOfPages;
                _beforeButton.gameObject.SetActive(_currentPage > 0);
                _nextButton.gameObject.SetActive(_currentPage < _numberOfPages - 1);
            }
        }

        public int NumberOfPages
        {
            get => _numberOfPages;
            set
            {
                _numberOfPages = value;
                RebuildButtons();
                CurrentPage = _currentPage;
            }
        }

        void RebuildButtons()
        {
            _beforeButton.onClick.RemoveAllListeners();
            _nextButton.onClick.RemoveAllListeners();

            _beforeButton.onClick.AddListener(() =>
            {
                OnClickEvent?.Invoke((_numberOfPages + _currentPage - 1) % _numberOfPages);
            });
            _nextButton.onClick.AddListener(() =>
            {
                OnClickEvent?.Invoke((_currentPage + 1) % _numberOfPages);
            });
        }
    }
}

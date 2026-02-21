using Cysharp.Text;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public class DiamondPurchaseHistoryView : UIView
    {
        [SerializeField] UIText _noContentText;
        [SerializeField] DiamondPurchaseHistoryCell[] _cells;
        [Header("ページ送り")]
        [SerializeField] UIText _pageText;
        [Header("ページ送り/ボタン")]
        [SerializeField] Button _firstPageButton;
        [SerializeField] GameObject _firstPageGrayOutObj;
        [SerializeField] Button _previousPageButton;
        [SerializeField] GameObject _previousPageGrayOutObj;
        [SerializeField] Button _nextPageButton;
        [SerializeField] GameObject _nextPageGrayOutObj;
        [SerializeField] Button _lastPageButton;
        [SerializeField] GameObject _lastPageGrayOutObj;

        public void InitializeView()
        {
            _firstPageButton.interactable = false;
            _previousPageButton.interactable = false;
            _nextPageButton.interactable = false;
            _lastPageButton.interactable = false;

            foreach (var cell in _cells)
            {
                cell.gameObject.SetActive(false);
            }
            _noContentText.gameObject.SetActive(false);

        }

        public void SetUpView(DiamondPurchaseHistoryViewModel model, PageNumber currentPage)
        {
            _firstPageButton.interactable = true;
            _previousPageButton.interactable = true;
            _nextPageButton.interactable = true;
            _lastPageButton.interactable = true;

            _noContentText.gameObject.SetActive(!model.HasElements);

            // ページテキストのセットアップ
            var formattedPageText = ZString.Format("{0}/{1}", currentPage.Value, model.MaxPage.Value);
            _pageText.SetText(formattedPageText);

            // セルのセットアップ
            SetUpCells(model, currentPage);
            // ボタンのセットアップ
            SetUpButton(model, currentPage);
        }

        void SetUpCells(DiamondPurchaseHistoryViewModel model, PageNumber currentPage)
        {
            var elements = model.FilterByCurrentPage(currentPage);
            for (int i = 0; i < _cells.Length; i++)
            {
                if (i < elements.Count)
                {
                    _cells[i].gameObject.SetActive(true);
                    _cells[i].SetUpCell(elements[i]);
                }
                else
                {
                    _cells[i].gameObject.SetActive(false);
                }
            }
        }

        // ボタン処理周り
        void SetUpButton(DiamondPurchaseHistoryViewModel viewModel, PageNumber currentPage)
        {
            _firstPageGrayOutObj.SetActive(!viewModel.CanGoToFirstPage(currentPage));
            _previousPageGrayOutObj.SetActive(!viewModel.CanGoToPreviousPage(currentPage));
            _nextPageGrayOutObj.SetActive(!viewModel.CanGoToNextPage(currentPage));
            _lastPageGrayOutObj.SetActive(!viewModel.CanGoToLastPage(currentPage));
        }
    }
}

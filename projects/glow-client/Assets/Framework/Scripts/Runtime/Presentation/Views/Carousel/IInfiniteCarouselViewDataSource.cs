namespace WPFramework.Presentation.Views
{
    public interface IInfiniteCarouselViewDataSource
    {
        int NumberOfItems();
        int SelectedIndex();
        InfiniteCarouselCell CellForItemAtIndex(int index);
    }
}

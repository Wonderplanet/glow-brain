namespace WPFramework.Presentation.Views
{
    public interface IInfiniteCarouselViewDelegate
    {
        void DidSelectItemAtIndex(int index);
        void DidLayoutCell(InfiniteCarouselCell cell, int index);
    }
}

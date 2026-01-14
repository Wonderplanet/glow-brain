namespace WPFramework.Presentation.Views
{
    public interface IInfiniteCarouselCellDelegate
    {
        void OnTap(int index);
        void OnPointerDown(int index);
    }
}

namespace WPFramework.Modules.Region
{
    public interface IApplicationRegionProvider
    {
        string RegionCode { get; }
        bool IsJapanRegion { get; }
    }
}

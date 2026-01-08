namespace WPFramework.Data.DataStores
{
    public sealed record RemoteSyncTargetData<T>(T Data, RemoteSyncType RemoteSyncType) where T : RemoteSyncData
    {
        public string Id => Data.Id;
        public T Data { get; } = Data;
        public RemoteSyncType RemoteSyncType { get; } = RemoteSyncType;
    }
}

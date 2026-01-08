using System;
using System.Collections.Generic;
using System.Linq;

namespace WPFramework.Modules.CacheDelete
{
    public sealed class CacheDeleter : IDisposable
    {
        bool _isDispose;

        List<IFileDeleteHandler> _handlers;

        public CacheDeleter(IFileDeleteHandler[] handlers)
        {
            _handlers = handlers.ToList();
        }

        public void Add(IFileDeleteHandler handler)
        {
            if (_isDispose)
            {
                return;
            }

            _handlers.Add(handler);
        }

        public void Delete()
        {
            if (_isDispose)
            {
                return;
            }

            foreach (var handler in _handlers)
            {
                handler.Delete();
            }
        }

        public void Dispose()
        {
            if (_isDispose)
            {
                return;
            }

            _isDispose = true;

            _handlers.Clear();
        }
    }
}

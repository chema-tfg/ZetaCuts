import React, { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useAuth } from '../contexts/AuthContext';
import '../styles/Modal.css';

const ProfileModal = ({ isOpen, onClose }) => {
  const { user } = useAuth();

useEffect(() => {
    if (isOpen) {
      
      const scrollY = window.scrollY;

document.body.style.position = 'fixed';
      document.body.style.top = `-${scrollY}px`;
      document.body.style.width = '100%';
      document.body.style.overflow = 'hidden';
    } else {
      
      const scrollY = document.body.style.top;
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      document.body.style.overflow = '';
      
      if (scrollY) {
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
      }
    }

return () => {
      const scrollY = document.body.style.top;
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      document.body.style.overflow = '';
      
      if (scrollY) {
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
      }
    };
  }, [isOpen]);

  if (!isOpen || !user) return null;

  const handleClose = () => {
    onClose();
  };

  const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
      handleClose();
    }
  };

  const modalContent = (
    <div 
      className="modal-overlay" 
      style={{ zIndex: 10000 }}
      onClick={handleOverlayClick}
    >
      <div className="modal-content auth-modal" style={{ zIndex: 10001 }}>
        <div className="modal-header">
          <h2>MI PERFIL</h2>
          <button className="modal-close" onClick={handleClose}>
            ×
          </button>
        </div>

        <div className="modal-body">
          <div className="profile-info">
            <div className="profile-field">
              <label className="profile-label">Nombre:</label>
              <div className="profile-value">{user.name || 'No especificado'}</div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Email:</label>
              <div className="profile-value">{user.email || 'No especificado'}</div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Teléfono:</label>
              <div className="profile-value">{user.phone || 'No especificado'}</div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Puntos:</label>
              <div className="profile-value points-value">{user.points || 0} pts</div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Rol:</label>
              <div className="profile-value">
                {user.is_admin ? 'Administrador' : user.is_barbero ? 'Barbero' : 'Cliente'}
              </div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Contraseña:</label>
              <div className="profile-value password-value">
                <span className="password-mask">••••••••••••</span>
                <span className="password-hint">(Protegida por seguridad)</span>
              </div>
            </div>

            <div className="profile-field">
              <label className="profile-label">Fecha de registro:</label>
              <div className="profile-value">
                {user.created_at 
                  ? new Date(user.created_at).toLocaleDateString('es-ES', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })
                  : 'No disponible'}
              </div>
            </div>
          </div>

          <div className="profile-actions">
            <button 
              type="button" 
              className="btn btn-secondary" 
              onClick={handleClose}
              style={{ width: '100%' }}
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  );

return createPortal(modalContent, document.body);
};

export default ProfileModal;

